<?php

namespace App\Http\Controllers;

use App\Mail\ApprobationMembershipMail;
use App\Models\FormulaireMembership;
use App\Models\InvitationMembership;
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
     * GET /api/participant/membership/acces
     * Vérifie si le participant connecté a une invitation active.
     */
    public function accesParticipantMembership(Request $request)
    {
        $user = $request->user();

        $invitation = InvitationMembership::query()
            ->where('id_user_participant', $user->id)
            ->where('status', 'En cours')
            ->orderByDesc('id')
            ->first();

        if (!$invitation) {
            return response()->json([
                'has_access' => false,
                'invitation' => null,
                'formulaire' => null,
            ]);
        }

        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'has_access' => true,
            'invitation' => $invitation,
            'formulaire' => $formulaire,
        ]);
    }

    /**
     * GET /api/organisateur/membership/participants/rechercher?email=
     * Recherche un participant existant pour invitation membership.
     */
    public function rechercherParticipantParEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()->where('email', $request->query('email'))->with('participant')->first();

        if (!$user || !$user->participant) {
            return response()->json([
                'message' => 'Aucun participant trouvé avec cette adresse email.',
            ], 404);
        }

        return response()->json([
            'id_user' => $user->id,
            'email' => $user->email,
            'prenom' => $user->participant->prenom,
            'nom' => $user->participant->nom,
        ]);
    }

    /**
     * POST /api/organisateur/membership/invitations
     * Organisateur invite un participant a completer son formulaire membership.
     */
    public function inviterParticipant(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'commentaire_admin' => 'nullable|string|max:500',
        ]);

        $admin = $request->user();
        $participantUser = User::query()->where('email', $validated['email'])->with('participant')->first();

        if (!$participantUser || !$participantUser->participant) {
            return response()->json([
                'message' => 'Aucun participant trouvé avec cette adresse email.',
            ], 404);
        }

        $activeInvitation = InvitationMembership::query()
            ->where('id_user_participant', $participantUser->id)
            ->where('status', 'En cours')
            ->first();

        if ($activeInvitation) {
            return response()->json([
                'message' => 'Une invitation membership est déjà en cours pour ce participant.',
                'invitation_id' => $activeInvitation->id,
            ], 409);
        }

        $invitation = InvitationMembership::create([
            'id_user_participant' => $participantUser->id,
            'id_admin_createur' => $admin->id,
            'commentaire_admin' => $validated['commentaire_admin'] ?? null,
            'status' => 'En cours',
            'date_invitation' => now(),
        ]);

        $participant = $participantUser->participant;

        $formulaire = FormulaireMembership::create([
            'id_invitation' => $invitation->id,
            'nom' => $participant->nom,
            'prenom' => $participant->prenom,
            'email' => $participantUser->email,
            'adresse' => $participant->adresse ?: 'A compléter',
            'code_postal' => $participant->code_postal ?: '0000',
            'ville' => $participant->ville ?: 'A compléter',
            'pays' => $participant->pays ?: 'Suisse',
            'telephone' => $participant->telephone ?: '000 000 00 00',
            'date_naissance' => $participant->date_naissance ?: now()->toDateString(),
            'description' => 'À compléter par le participant.',
            'status' => 'À compléter',
            'date_creation' => now(),
            'prix' => 25.00,
        ]);

        $this->notifyMembershipInvitationCreated($invitation, $admin, $participantUser);

        return response()->json([
            'message' => 'Invitation membership envoyée avec succès.',
            'invitation_id' => $invitation->id,
            'formulaire_id' => $formulaire->id,
        ], 201);
    }

    /**
     * GET /api/participant/membership/ma-demande
     * Retourne le dernier formulaire membership du participant connecté.
     */
    public function maDemande(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(null);
        }

        $demande = FormulaireMembership::query()
            ->whereHas('invitation', function ($query) use ($user) {
                $query->where('id_user_participant', $user->id);
            })
            ->orderByDesc('id')
            ->first();

        return response()->json($demande);
    }

    /**
     * POST /api/participant/membership/demander
     * Participant complete et soumet son formulaire membership.
     */
    public function soumettreDemande(Request $request)
    {
        $user = $request->user();

        $invitation = InvitationMembership::query()
            ->where('id_user_participant', $user->id)
            ->where('status', 'En cours')
            ->orderByDesc('id')
            ->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'Aucune invitation membership active pour ce compte.',
            ], 403);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'date_naissance' => 'required|date',
            'description' => 'required|string|min:10|max:1000',
        ]);

        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->orderByDesc('id')
            ->first();

        if (!$formulaire) {
            $formulaire = new FormulaireMembership();
            $formulaire->id_invitation = $invitation->id;
        }

        $formulaire->fill(array_merge($validated, [
            'email' => $user->email,
            'status' => 'En attente de validation',
            'date_creation' => now(),
            'date_decision' => null,
            'id_admin_decideur' => null,
            'notes_admin' => null,
            'prix' => 25.00,
        ]));
        $formulaire->save();

        $invitation->update([
            'status' => 'Complété',
        ]);

        $this->notifyAdminsMembershipFormCompleted($formulaire, $user);

        return response()->json([
            'message' => 'Votre formulaire membership a été soumis avec succès.',
            'demande_id' => $formulaire->id,
        ], 201);
    }

    /**
     * GET /api/admin/membership/demandes
     * Récupère tous les formulaires de membership.
     */
    public function listerDemandes()
    {
        $demandes = FormulaireMembership::query()
            ->with('invitation')
            ->orderByDesc('date_creation')
            ->get()
            ->map(function (FormulaireMembership $demande) {
                $payload = $demande->toArray();
                $payload['invitation_status'] = $demande->invitation?->status;
                $payload['id_invitation'] = $demande->id_invitation;

                return $payload;
            });

        return response()->json($demandes);
    }

    /**
     * GET /api/admin/membership/demandes/en-attente
     * Récupère les formulaires en attente de validation.
     */
    public function listerDemandesEnAttente()
    {
        $demandes = FormulaireMembership::where('status', 'En attente de validation')
            ->orderByDesc('date_creation')
            ->get();

        return response()->json($demandes);
    }

    /**
     * POST /api/admin/membership/demandes/{id}/approuver
     * Admin approuve un formulaire membership.
     */
    public function approuverDemande(Request $request, $id)
    {
        $demande = FormulaireMembership::findOrFail($id);

        if ($demande->status !== 'En attente de validation') {
            return response()->json([
                'message' => 'Ce formulaire n\'est pas en attente de validation.',
            ], 409);
        }

        try {
            DB::beginTransaction();

            $existingUser = User::where('email', $demande->email)
                ->with('participant')
                ->first();

            if (!$existingUser || !$existingUser->participant) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Aucun utilisateur trouvé avec cette adresse email.',
                ], 409);
            }

            $roleMembre = DB::table('Role')->where('type', 'Membre')->first();

            if (!$roleMembre) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Le rôle Membre n\'existe pas.',
                ], 500);
            }

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

            $demande->update([
                'status' => 'Approuvée',
                'date_decision' => now(),
                'id_admin_decideur' => Auth::user()->id,
            ]);

            $invitation = InvitationMembership::find($demande->id_invitation);
            if ($invitation && $invitation->status !== 'Annulé') {
                $invitation->update(['status' => 'Complété']);
            }

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
     * POST /api/admin/membership/demandes/{id}/a-completer
     * Admin remet un formulaire a l'etat A completer.
     */
    public function remettreACompleterDemande(Request $request, $id)
    {
        $validated = $request->validate([
            'commentaire_admin' => 'required|string|max:500',
        ]);

        $demande = FormulaireMembership::findOrFail($id);

        if ($demande->status !== 'En attente de validation') {
            return response()->json([
                'message' => 'Ce formulaire ne peut pas être remis à compléter dans son état actuel.',
            ], 409);
        }

        $demande->update([
            'status' => 'À compléter',
            'date_decision' => now(),
            'id_admin_decideur' => Auth::user()->id,
            'notes_admin' => $validated['commentaire_admin'],
        ]);

        $this->supprimerNotificationsDemandeMembership($demande->id, 'new_membership_request');

        $invitation = InvitationMembership::find($demande->id_invitation);
        if ($invitation && $invitation->status !== 'Annulé') {
            $invitation->update([
                'status' => 'En cours',
            ]);
        }

        return response()->json([
            'message' => 'Le formulaire a été remis à compléter.',
        ], 200);
    }

    /**
     * POST /api/admin/membership/invitations/{id}/annuler
     * Annule une invitation membership active.
     */
    public function annulerInvitation(Request $request, int $id)
    {
        $validated = $request->validate([
            'commentaire_annulation' => 'nullable|string|max:500',
        ]);

        $invitation = InvitationMembership::findOrFail($id);

        if ($invitation->status === 'Annulé') {
            return response()->json([
                'message' => 'Cette invitation est déjà annulée.',
            ], 409);
        }

        $admin = $request->user();
        $participantUser = User::with('participant')->find($invitation->id_user_participant);

        $invitation->update([
            'status' => 'Annulé',
            'date_annulation' => now(),
            'id_admin_annulation' => $admin->id,
            'commentaire_annulation' => $validated['commentaire_annulation'] ?? null,
        ]);

        FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->whereIn('status', ['À compléter', 'En attente de validation'])
            ->update([
                'status' => 'Annulé',
                'date_decision' => now(),
                'id_admin_decideur' => $admin->id,
                'notes_admin' => $validated['commentaire_annulation'] ?? 'Invitation annulée.',
            ]);

        if ($participantUser) {
            $this->notifyMembershipInvitationCancelled($invitation, $admin, $participantUser);
        }

        return response()->json([
            'message' => 'Invitation membership annulée avec succès.',
        ]);
    }

    /**
     * POST /api/participant/membership/checkout
     * Called at payment time to finalize the membership form and grant the member role.
     */
    public function checkoutPayment(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'formulaire' => 'required|array',
            'formulaire.nom' => 'required|string|max:100',
            'formulaire.prenom' => 'required|string|max:100',
            'formulaire.adresse' => 'required|string|max:255',
            'formulaire.code_postal' => 'required|string|max:10',
            'formulaire.ville' => 'required|string|max:100',
            'formulaire.pays' => 'required|string|max:100',
            'formulaire.telephone' => 'required|string|max:20',
            'formulaire.date_naissance' => 'required|date',
            'formulaire.description' => 'required|string|min:10|max:1000',
            'invitation_id' => 'nullable|integer|exists:InvitationMembership,id',
            'prix' => 'required|numeric',
        ]);

        // Find the invitation either by provided id or last active invitation for the user
        $invitation = null;
        if (!empty($validated['invitation_id'])) {
            $invitation = InvitationMembership::find($validated['invitation_id']);
        }

        if (!$invitation) {
            $invitation = InvitationMembership::query()
                ->where('id_user_participant', $user->id)
                ->where('status', 'En cours')
                ->orderByDesc('id')
                ->first();
        }

        if (!$invitation) {
            return response()->json(['message' => 'Aucune invitation active trouvée pour ce compte.'], 403);
        }

        // Create or update the formulaire linked to the invitation
        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->orderByDesc('id')
            ->first();

        if (!$formulaire) {
            $formulaire = new FormulaireMembership();
            $formulaire->id_invitation = $invitation->id;
        }

        $data = $validated['formulaire'];
        $data['email'] = $user->email;
        $data['prix'] = $validated['prix'];
        $data['status'] = 'Approuvée';
        $data['date_creation'] = now();
        $data['date_decision'] = now();
        $data['id_admin_decideur'] = null;

        $formulaire->fill($data);
        $formulaire->save();

        // Mark invitation completed
        if ($invitation->status !== 'Annulé') {
            $invitation->update(['status' => 'Complété']);
        }

        // Assign role Membre to the user
        try {
            DB::beginTransaction();

            $existingUser = User::where('id', $user->id)->with('participant')->first();
            $roleMembre = DB::table('Role')->where('type', 'Membre')->first();

            if ($roleMembre) {
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
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'assignation du rôle Membre: ' . $e->getMessage());
        }

        // Notify admins and send confirmation email
        $this->notifyAdminsMembershipFormCompleted($formulaire, $user);

        try {
            Mail::to($user->email)->send(new ApprobationMembershipMail($formulaire));
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi email approbation membership (checkout) : " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Membership finalisé et rôle attribué.',
            'formulaire_id' => $formulaire->id,
        ], 201);
    }

    /**
     * Conserve l'appel historique sans recréer l'ancienne notification dédiée.
     */
    private function notifyAdminsMembershipApproved(FormulaireMembership $demande, User $adminActeur): void
    {
        // Intentionally left blank.
    }

    /**
     * Notifie le participant invite et les autres admins.
     */
    private function notifyMembershipInvitationCreated(InvitationMembership $invitation, User $adminCreateur, User $participantUser): void
    {
        $participantPrenom = $participantUser->participant?->prenom ?? 'Participant';
        $participantNom = $participantUser->participant?->nom ?? '';

        Message::create([
            'content' => json_encode([
                'type' => 'membership_invitation_to_complete',
                'recipient_user_id' => $participantUser->id,
                'invitation_id' => $invitation->id,
                'admin_prenom' => $adminCreateur->participant?->prenom,
                'admin_nom' => $adminCreateur->participant?->nom,
                'commentaire_admin' => $invitation->commentaire_admin,
            ]),
        ]);

        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->where('id', '!=', $adminCreateur->id)->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type' => 'membership_invitation_info',
                    'recipient_user_id' => $admin->id,
                    'invitation_id' => $invitation->id,
                    'participant_prenom' => $participantPrenom,
                    'participant_nom' => $participantNom,
                    'participant_email' => $participantUser->email,
                    'admin_prenom' => $adminCreateur->participant?->prenom,
                    'admin_nom' => $adminCreateur->participant?->nom,
                ]),
            ]);
        }
    }

    /**
     * Notifie les admins qu'un participant a complete son formulaire.
     */
    private function notifyAdminsMembershipFormCompleted(FormulaireMembership $demande, User $participantUser): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type' => 'new_membership_request',
                    'recipient_user_id' => $admin->id,
                    'demande_id' => $demande->id,
                    'prenom' => $participantUser->participant?->prenom ?? $demande->prenom,
                    'nom' => $participantUser->participant?->nom ?? $demande->nom,
                    'email' => $participantUser->email,
                ]),
            ]);
        }
    }

    /**
     * Notifie les autres admins et le participant quand l'invitation est annulee.
     */
    private function notifyMembershipInvitationCancelled(InvitationMembership $invitation, User $adminActeur, User $participantUser): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->where('id', '!=', $adminActeur->id)->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type' => 'membership_invitation_cancelled_info',
                    'recipient_user_id' => $admin->id,
                    'invitation_id' => $invitation->id,
                    'participant_prenom' => $participantUser->participant?->prenom,
                    'participant_nom' => $participantUser->participant?->nom,
                    'admin_prenom' => $adminActeur->participant?->prenom,
                    'admin_nom' => $adminActeur->participant?->nom,
                ]),
            ]);
        }

        Message::create([
            'content' => json_encode([
                'type' => 'membership_invitation_cancelled_participant',
                'recipient_user_id' => $participantUser->id,
                'invitation_id' => $invitation->id,
                'admin_prenom' => $adminActeur->participant?->prenom,
                'admin_nom' => $adminActeur->participant?->nom,
            ]),
        ]);
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

}
