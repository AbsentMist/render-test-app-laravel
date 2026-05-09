<?php

namespace App\Http\Controllers;

use App\Mail\ApprobationMembershipMail;
use App\Models\DemandeMembership;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MembershipController extends Controller
{
    /**
     * POST /api/membership/demander
     * Participant remplit le formulaire et soumet une demande de membership
     */
    public function soumettreDemande(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'date_naissance' => 'required|date',
            'description' => 'required|string|min:10|max:1000',
        ]);

        $existingUser = User::where('email', $validated['email'])
            ->with('participant')
            ->first();

        if (!$existingUser || !$existingUser->participant) {
            return response()->json([
                'message' => 'Aucun utilisateur trouvé avec cette adresse email.',
                'errors' => [
                    'email' => ['Aucun utilisateur trouvé avec cette adresse email.'],
                ],
            ], 422);
        }

        $derniereDemande = DemandeMembership::where('email', $validated['email'])
            ->orderByDesc('id')
            ->first();

        if ($derniereDemande && $derniereDemande->status === 'En attente') {
            return response()->json([
                'message' => 'Une demande de membership est deja en cours pour cette adresse email.',
                'status' => 'En attente',
                'demande_id' => $derniereDemande->id,
            ], 409);
        }

        if ($derniereDemande && $derniereDemande->status === 'Approuvée') {
            return response()->json([
                'message' => 'Cette adresse email a deja une demande approuvee. Aucun nouvel envoi n est necessaire.',
                'status' => 'Approuvée',
                'demande_id' => $derniereDemande->id,
            ], 409);
        }

        if ($derniereDemande && $derniereDemande->status === 'Refusée') {
            $derniereDemande->update([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'adresse' => $validated['adresse'],
                'code_postal' => $validated['code_postal'],
                'ville' => $validated['ville'],
                'pays' => $validated['pays'],
                'telephone' => $validated['telephone'],
                'date_naissance' => $validated['date_naissance'],
                'description' => $validated['description'],
                'status' => 'En attente',
                'date_creation' => now(),
                'date_decision' => null,
                'id_admin_decideur' => null,
                'notes_admin' => null,
            ]);

            $demande = $derniereDemande;
        } else {
            $demande = DemandeMembership::create($validated);
        }

        // Notifier tous les admins
        $this->notifyAdminsNewMembershipRequest($demande);

        return response()->json([
            'message' => 'Votre demande de membership a été enregistrée avec succès.',
            'demande_id' => $demande->id,
        ], 201);
    }

    /**
     * GET /api/participant/membership/ma-demande
     * Retourne la derniere demande du participant connecte selon son email
     */
    public function maDemande(Request $request)
    {
        $email = $request->user()?->email;

        if (!$email) {
            return response()->json(null);
        }

        $demande = DemandeMembership::where('email', $email)
            ->orderByDesc('id')
            ->first();

        return response()->json($demande);
    }

    /**
     * GET /api/admin/membership/demandes
     * Récupère toutes les demandes de membership (accès admin uniquement)
     */
    public function listerDemandes()
    {
        $demandes = DemandeMembership::orderByDesc('date_creation')->get();

        return response()->json($demandes);
    }

    /**
     * GET /api/admin/membership/demandes/en-attente
     * Récupère uniquement les demandes en attente (accès admin uniquement)
     */
    public function listerDemandesEnAttente()
    {
        $demandes = DemandeMembership::where('status', 'En attente')
            ->orderByDesc('date_creation')
            ->get();

        return response()->json($demandes);
    }

    /**
     * POST /api/admin/membership/demandes/{id}/approuver
     * Admin approuve une demande de membership
        * - Assigne le rôle Membre à l'utilisateur existant
     * - Notifie les autres admins
     */
    public function approuverDemande(Request $request, $id)
    {
        $demande = DemandeMembership::findOrFail($id);

        // Vérifier que la demande est bien en attente
        if ($demande->status !== 'En attente') {
            return response()->json([
                'message' => 'Cette demande a déjà été traitée.',
            ], 409);
        }

        try {
            DB::beginTransaction();

            // 1. Retrouver le compte existant lié à l'email de la demande
            $existingUser = User::where('email', $demande->email)
                ->with('participant')
                ->first();

            if (!$existingUser || !$existingUser->participant) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Aucun utilisateur trouvé avec cette adresse email.',
                ], 409);
            }

            // 2. Récupérer le rôle Membre
            $roleMembre = DB::table('Role')->where('type', 'Membre')->first();

            if (!$roleMembre) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Le rôle Membre n\'existe pas.',
                ], 500);
            }

            // 3. Assigner le rôle Membre si nécessaire
            $roleDejaAssigne = DB::table('UserRole')
                ->where('id_user', $existingUser->id)
                ->where('id_role', $roleMembre->id)
                ->exists();

            if (!$roleDejaAssigne) {
                DB::table('UserRole')->insert([
                    'id_user' => $existingUser->id,
                    'id_role' => $roleMembre->id,
                ]);
            }

            // 4. Mettre à jour la demande
            $demande->update([
                'status' => 'Approuvée',
                'date_decision' => now(),
                'id_admin_decideur' => Auth::user()->id,
            ]);

            $this->supprimerNotificationsDemandeMembership($demande->id, 'new_membership_request');

            DB::commit();

            $this->notifyAdminsMembershipApproved($demande, Auth::user());

            try {
                Mail::to($demande->email)->send(new ApprobationMembershipMail($demande));
            } catch (\Exception $e) {
                Log::error("Erreur d'envoi email approbation membership : " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Demande approuvée avec succès.',
                'membership' => [
                    'id' => $existingUser->id,
                    'email' => $existingUser->email,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'approbation membership : " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'approbation : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/admin/membership/demandes/{id}/refuser
     * Admin refuse une demande de membership
     */
    public function refuserDemande(Request $request, $id)
    {
        $validated = $request->validate([
            'raison' => 'required|string|max:500',
        ]);

        $demande = DemandeMembership::findOrFail($id);

        // Vérifier que la demande est bien en attente
        if ($demande->status !== 'En attente') {
            return response()->json([
                'message' => 'Cette demande a déjà été traitée.',
            ], 409);
        }

        // Mettre à jour la demande
        $demande->update([
            'status' => 'Refusée',
            'date_decision' => now(),
            'id_admin_decideur' => Auth::user()->id,
            'notes_admin' => $validated['raison'],
        ]);

        $this->supprimerNotificationsDemandeMembership($demande->id, 'new_membership_request');

        $this->notifyAdminsMembershipRefused($demande, Auth::user());

        return response()->json([
            'message' => 'Demande refusée avec succès.',
        ], 200);
    }

    /**
     * PRIVÉ : Notifie tous les admins d'une nouvelle demande de membership
     */
    private function notifyAdminsNewMembershipRequest(DemandeMembership $demande)
    {
        // Récupérer tous les admins
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type' => 'new_membership_request',
                    'recipient_user_id' => $admin->id,
                    'demande_id' => $demande->id,
                    'prenom' => $demande->prenom,
                    'nom' => $demande->nom,
                    'email' => $demande->email,
                    'description' => $demande->description,
                ]),
            ]);
        }
    }

    /**
     * Supprime toutes les notifications liées à une demande membership.
     */
    private function notifyAdminsMembershipApproved(DemandeMembership $demande, User $adminDecideur): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->where('id', '!=', $adminDecideur->id)->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type' => 'membership_approved_info',
                    'recipient_user_id' => $admin->id,
                    'demande_id' => $demande->id,
                    'prenom' => $demande->prenom,
                    'nom' => $demande->nom,
                    'admin_decideur_email' => $adminDecideur->email,
                ]),
            ]);
        }
    }

    /**
     * PRIVÉ : Notifie les autres admins qu'une demande a été refusée
     */
    private function notifyAdminsMembershipRefused(DemandeMembership $demande, User $adminDecideur): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->where('id', '!=', $adminDecideur->id)->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type' => 'membership_refused_info',
                    'recipient_user_id' => $admin->id,
                    'demande_id' => $demande->id,
                    'prenom' => $demande->prenom,
                    'nom' => $demande->nom,
                    'admin_decideur_email' => $adminDecideur->email,
                ]),
            ]);
        }
    }

    /**
     * Supprime toutes les notifications liées à une demande membership pour un type donné.
     */
    private function supprimerNotificationsDemandeMembership(int $demandeId, string $type): void
    {
        $messages = Message::orderByDesc('id')->get();

        foreach ($messages as $message) {
            $payload = json_decode($message->content, true);

            if (!is_array($payload)) {
                continue;
            }

            if (($payload['demande_id'] ?? null) !== $demandeId) {
                continue;
            }

            if (($payload['type'] ?? null) === $type) {
                $message->delete();
            }
        }
    }

    /**
     * GET /api/participant/rechercher-participant?email=...
     * Vérifie qu'un utilisateur avec participant existe pour l'email fourni.
     */
    public function verifierEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)
            ->with('participant')
            ->first();

        if (!$user || !$user->participant) {
            return response()->json([
                'message' => 'Aucun utilisateur trouvé avec cette adresse email.',
            ], 404);
        }

        return response()->json([
            'message' => 'Utilisateur trouvé.',
            'email' => $user->email,
        ]);
    }
}
