<?php

/**
 * @fileoverview MembershipController.php
 * @description Contrôleur gérant le cycle de vie complet des demandes de membership :
 *              invitation par un admin, soumission du formulaire par le participant,
 *              validation/refus/renvoi par l'admin, annulation d'invitation,
 *              finalisation au moment du paiement et système de notifications internes.
 * @author Ngoie Steven
 */

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
     * Vérifie si le participant connecté possède une invitation membership active
     * et retourne l'invitation ainsi que le formulaire associé le cas échéant.
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse `{has_access, invitation, formulaire}`.
     */
    public function accesParticipantMembership(Request $request)
    {
        $user = $request->user();

        // Recherche la dernière invitation active (status "En cours") pour ce compte
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

        // Récupère le formulaire lié à cette invitation si déjà créé
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
     * Recherche un participant existant par adresse email pour lui envoyer une invitation membership.
     * Expose uniquement les données publiques nécessaires à l'interface admin.
     * @author Ngoie Steven
     * @param  Request $request Doit contenir le paramètre query `email`.
     * @return \Illuminate\Http\JsonResponse Données publiques du participant ou 404.
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
            'email'   => $user->email,
            'prenom'  => $user->participant->prenom,
            'nom'     => $user->participant->nom,
        ]);
    }

    /**
     * Crée une invitation membership pour un participant existant.
     * Pré-remplit automatiquement le formulaire avec les données du profil participant.
     * Bloque la création si une invitation est déjà en cours pour ce participant.
     * Notifie le participant invité et les autres administrateurs.
     * @author Ngoie Steven
     * @param  Request $request Doit contenir `email` et optionnellement `commentaire_admin`.
     * @return \Illuminate\Http\JsonResponse IDs de l'invitation et du formulaire créés (201).
     */
    public function inviterParticipant(Request $request)
    {
        $validated = $request->validate([
            'email'             => 'required|email',
            'commentaire_admin' => 'nullable|string|max:500',
        ]);

        $admin           = $request->user();
        $participantUser = User::query()->where('email', $validated['email'])->with('participant')->first();

        if (!$participantUser || !$participantUser->participant) {
            return response()->json([
                'message' => 'Aucun participant trouvé avec cette adresse email.',
            ], 404);
        }

        // Bloque si une invitation est déjà active pour ce participant
        $activeInvitation = InvitationMembership::query()
            ->where('id_user_participant', $participantUser->id)
            ->where('status', 'En cours')
            ->first();

        if ($activeInvitation) {
            return response()->json([
                'message'      => 'Une invitation membership est déjà en cours pour ce participant.',
                'invitation_id'=> $activeInvitation->id,
            ], 409);
        }

        $invitation = InvitationMembership::create([
            'id_user_participant' => $participantUser->id,
            'id_admin_createur'   => $admin->id,
            'commentaire_admin'   => $validated['commentaire_admin'] ?? null,
            'status'              => 'En cours',
            'date_invitation'     => now(),
        ]);

        $participant = $participantUser->participant;

        // Pré-remplit le formulaire avec les données existantes du profil ; le participant les complétera
        $formulaire = FormulaireMembership::create([
            'id_invitation'  => $invitation->id,
            'nom'            => $participant->nom,
            'prenom'         => $participant->prenom,
            'email'          => $participantUser->email,
            'adresse'        => $participant->adresse ?: 'A compléter',
            'code_postal'    => $participant->code_postal ?: '0000',
            'ville'          => $participant->ville ?: 'A compléter',
            'pays'           => $participant->pays ?: 'Suisse',
            'telephone'      => $participant->telephone ?: '000 000 00 00',
            'date_naissance' => $participant->date_naissance ?: now()->toDateString(),
            'description'    => 'À compléter par le participant.',
            'status'         => 'À compléter',
            'date_creation'  => now(),
            'prix'           => 25.00,
        ]);

        $this->notifyMembershipInvitationCreated($invitation, $admin, $participantUser);

        return response()->json([
            'message'       => 'Invitation membership envoyée avec succès.',
            'invitation_id' => $invitation->id,
            'formulaire_id' => $formulaire->id,
        ], 201);
    }

    /**
     * Retourne le dernier formulaire membership soumis par le participant connecté.
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse Formulaire membership ou null si aucun.
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
     * Soumet le formulaire membership rempli par le participant.
     * Passe le formulaire au statut "En attente de validation" et marque l'invitation "Complété".
     * Notifie tous les administrateurs qu'une nouvelle demande est à traiter.
     * @author Ngoie Steven
     * @param  Request $request Données complètes du formulaire (nom, prénom, adresse, description...).
     * @return \Illuminate\Http\JsonResponse ID du formulaire soumis (201) ou 403 si pas d'invitation active.
     */
    public function soumettreDemande(Request $request)
    {
        $user = $request->user();

        // Vérifie qu'une invitation est bien active pour ce compte avant d'accepter la soumission
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
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'adresse'        => 'required|string|max:255',
            'code_postal'    => 'required|string|max:10',
            'ville'          => 'required|string|max:100',
            'pays'           => 'required|string|max:100',
            'telephone'      => 'required|string|max:20',
            'date_naissance' => 'required|date',
            'description'    => 'required|string|min:10|max:1000',
        ]);

        // Réutilise le formulaire existant s'il y en a un, sinon en crée un nouveau
        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->orderByDesc('id')
            ->first();

        if (!$formulaire) {
            $formulaire = new FormulaireMembership();
            $formulaire->id_invitation = $invitation->id;
        }

        $formulaire->fill(array_merge($validated, [
            'email'             => $user->email,
            'status'            => 'En attente de validation',
            'date_creation'     => now(),
            'date_decision'     => null,
            'id_admin_decideur' => null,
            'notes_admin'       => null,
            'prix'              => 25.00,
        ]));
        $formulaire->save();

        $invitation->update(['status' => 'Complété']);

        $this->notifyAdminsMembershipFormCompleted($formulaire, $user);

        return response()->json([
            'message'    => 'Votre formulaire membership a été soumis avec succès.',
            'demande_id' => $formulaire->id,
        ], 201);
    }

    /**
     * Retourne tous les formulaires membership pour la vue administrateur.
     * Inclut le statut et l'ID de l'invitation associée pour chaque demande.
     * @author Ngoie Steven
     * @return \Illuminate\Http\JsonResponse Liste de toutes les demandes triées par date de création décroissante.
     */
    public function listerDemandes()
    {
        $demandes = FormulaireMembership::query()
            ->with('invitation')
            ->orderByDesc('date_creation')
            ->get()
            ->map(function (FormulaireMembership $demande) {
                $payload = $demande->toArray();
                // Ajoute le statut de l'invitation pour affichage dans le tableau admin
                $payload['invitation_status'] = $demande->invitation?->status;
                $payload['id_invitation']     = $demande->id_invitation;
                return $payload;
            });

        return response()->json($demandes);
    }

    /**
     * Retourne uniquement les formulaires membership en attente de validation.
     * Utilisé pour l'indicateur de badge dans l'interface administrateur.
     * @author Ngoie Steven
     * @return \Illuminate\Http\JsonResponse Liste des demandes en attente.
     */
    public function listerDemandesEnAttente()
    {
        $demandes = FormulaireMembership::where('status', 'En attente de validation')
            ->orderByDesc('date_creation')
            ->get();

        return response()->json($demandes);
    }

    /**
     * Approuve une demande de membership.
     * Assigne le rôle "Membre" à l'utilisateur concerné (idempotent : vérifie si déjà attribué),
     * met à jour le statut du formulaire et de l'invitation, supprime les notifications en attente
     * et envoie un email de confirmation au participant.
     * L'ensemble des opérations en base est enveloppé dans une transaction.
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée (admin).
     * @param  int     $id      Identifiant du formulaire membership à approuver.
     * @return \Illuminate\Http\JsonResponse Confirmation (200) ou erreur (409/500).
     */
    public function approuverDemande(Request $request, $id)
    {
        $demande = FormulaireMembership::findOrFail($id);

        // Bloque si le formulaire n'est plus dans un état approuvable
        if ($demande->status !== 'En attente de validation') {
            return response()->json([
                'message' => 'Ce formulaire n\'est pas en attente de validation.',
            ], 409);
        }

        try {
            DB::beginTransaction();

            $existingUser = User::where('email', $demande->email)->with('participant')->first();

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

            // Vérifie avant d'insérer pour éviter les doublons dans la table pivot UserRole
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
                'status'            => 'Approuvée',
                'date_decision'     => now(),
                'id_admin_decideur' => Auth::user()->id,
            ]);

            // Marque l'invitation comme complétée sauf si elle a été annulée entretemps
            $invitation = InvitationMembership::find($demande->id_invitation);
            if ($invitation && $invitation->status !== 'Annulé') {
                $invitation->update(['status' => 'Complété']);
            }

            // Supprime les notifications "new_membership_request" qui ne sont plus pertinentes
            $this->supprimerNotificationsDemandeMembership($demande->id, 'new_membership_request');

            DB::commit();

            $this->notifyAdminsMembershipApproved($demande, Auth::user());

            // Email non-bloquant : l'approbation reste effective même en cas d'échec d'envoi
            try {
                Mail::to($demande->email)->send(new ApprobationMembershipMail($demande));
            } catch (\Exception $e) {
                Log::error("Erreur d'envoi email approbation membership : " . $e->getMessage());
            }

            return response()->json([
                'message'    => 'Demande approuvée avec succès.',
                'membership' => [
                    'id'    => $existingUser->id,
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
     * Remet un formulaire membership au statut "À compléter" avec un commentaire admin.
     * Utilisé lorsque le formulaire soumis est incomplet ou incorrect.
     * Réouvre l'invitation associée pour permettre au participant de soumettre à nouveau.
     * @author Ngoie Steven
     * @param  Request $request Doit contenir `commentaire_admin` (requis).
     * @param  int     $id      Identifiant du formulaire à remettre en état "À compléter".
     * @return \Illuminate\Http\JsonResponse Message de confirmation (200) ou 409 si état invalide.
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
            'status'            => 'À compléter',
            'date_decision'     => now(),
            'id_admin_decideur' => Auth::user()->id,
            'notes_admin'       => $validated['commentaire_admin'],
        ]);

        // Nettoie les notifications obsolètes liées à cette demande
        $this->supprimerNotificationsDemandeMembership($demande->id, 'new_membership_request');

        // Réouvre l'invitation pour que le participant puisse soumettre à nouveau
        $invitation = InvitationMembership::find($demande->id_invitation);
        if ($invitation && $invitation->status !== 'Annulé') {
            $invitation->update(['status' => 'En cours']);
        }

        return response()->json([
            'message' => 'Le formulaire a été remis à compléter.',
        ], 200);
    }

    /**
     * Annule une invitation membership active.
     * Cascade : annule également les formulaires associés encore en cours de traitement.
     * Notifie le participant concerné et les autres administrateurs.
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée (admin). Peut contenir `commentaire_annulation`.
     * @param  int     $id      Identifiant de l'invitation à annuler.
     * @return \Illuminate\Http\JsonResponse Confirmation ou 409 si déjà annulée.
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

        $admin           = $request->user();
        $participantUser = User::with('participant')->find($invitation->id_user_participant);

        $invitation->update([
            'status'                 => 'Annulé',
            'date_annulation'        => now(),
            'id_admin_annulation'    => $admin->id,
            'commentaire_annulation' => $validated['commentaire_annulation'] ?? null,
        ]);

        // Annule en cascade tous les formulaires encore actifs liés à cette invitation
        FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->whereIn('status', ['À compléter', 'En attente de validation'])
            ->update([
                'status'            => 'Annulé',
                'date_decision'     => now(),
                'id_admin_decideur' => $admin->id,
                'notes_admin'       => $validated['commentaire_annulation'] ?? 'Invitation annulée.',
            ]);

        if ($participantUser) {
            $this->notifyMembershipInvitationCancelled($invitation, $admin, $participantUser);
        }

        return response()->json([
            'message' => 'Invitation membership annulée avec succès.',
        ]);
    }

    /**
     * Finalise la demande de membership au moment du paiement.
     * Crée ou met à jour le formulaire, marque l'invitation comme complétée,
     * assigne le rôle "Membre" à l'utilisateur et envoie l'email de confirmation.
     * Appelé depuis le frontend après validation du paiement côté panier.
     * @author Ngoie Steven
     * @param  Request $request Doit contenir `formulaire` (objet), `prix` et optionnellement `invitation_id`.
     * @return \Illuminate\Http\JsonResponse ID du formulaire finalisé (201) ou 403 si pas d'invitation active.
     */
    public function checkoutPayment(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'formulaire'                  => 'required|array',
            'formulaire.nom'              => 'required|string|max:100',
            'formulaire.prenom'           => 'required|string|max:100',
            'formulaire.adresse'          => 'required|string|max:255',
            'formulaire.code_postal'      => 'required|string|max:10',
            'formulaire.ville'            => 'required|string|max:100',
            'formulaire.pays'             => 'required|string|max:100',
            'formulaire.telephone'        => 'required|string|max:20',
            'formulaire.date_naissance'   => 'required|date',
            'formulaire.description'      => 'required|string|min:10|max:1000',
            'invitation_id'               => 'nullable|integer|exists:InvitationMembership,id',
            'prix'                        => 'required|numeric',
        ]);

        // Cherche l'invitation par ID fourni ou par la dernière invitation active du compte
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

        // Réutilise le formulaire existant ou en crée un nouveau
        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->orderByDesc('id')
            ->first();

        if (!$formulaire) {
            $formulaire = new FormulaireMembership();
            $formulaire->id_invitation = $invitation->id;
        }

        $data                    = $validated['formulaire'];
        $data['email']           = $user->email;
        $data['prix']            = $validated['prix'];
        $data['status']          = 'Approuvée'; // Paiement validé = membership directement approuvé
        $data['date_creation']   = now();
        $data['date_decision']   = now();
        $data['id_admin_decideur'] = null;

        $formulaire->fill($data);
        $formulaire->save();

        if ($invitation->status !== 'Annulé') {
            $invitation->update(['status' => 'Complété']);
        }

        // Assigne le rôle Membre dans une transaction pour garantir la cohérence
        try {
            DB::beginTransaction();

            $existingUser = User::where('id', $user->id)->with('participant')->first();
            $roleMembre   = DB::table('Role')->where('type', 'Membre')->first();

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

        $this->notifyAdminsMembershipFormCompleted($formulaire, $user);

        // Email non-bloquant
        try {
            Mail::to($user->email)->send(new ApprobationMembershipMail($formulaire));
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi email approbation membership (checkout) : " . $e->getMessage());
        }

        return response()->json([
            'message'       => 'Membership finalisé et rôle attribué.',
            'formulaire_id' => $formulaire->id,
        ], 201);
    }

    /**
     * Stub conservé pour compatibilité avec les appels historiques.
     * L'ancienne notification dédiée à l'approbation a été supprimée ; cette méthode reste vide.
     * @author Ngoie Steven
     * @param  FormulaireMembership $demande     Demande approuvée.
     * @param  User                 $adminActeur Admin ayant effectué l'approbation.
     * @return void
     */
    private function notifyAdminsMembershipApproved(FormulaireMembership $demande, User $adminActeur): void
    {
        // Intentionally left blank.
    }

    /**
     * Envoie une notification au participant invité et une information aux autres administrateurs.
     * La notification du participant contient l'ID de l'invitation pour permettre
     * le filtrage côté frontend (masquage si l'invitation n'est plus active).
     * @author Ngoie Steven
     * @param  InvitationMembership $invitation      Invitation nouvellement créée.
     * @param  User                 $adminCreateur   Admin ayant envoyé l'invitation.
     * @param  User                 $participantUser Utilisateur invité.
     * @return void
     */
    private function notifyMembershipInvitationCreated(InvitationMembership $invitation, User $adminCreateur, User $participantUser): void
    {
        $participantPrenom = $participantUser->participant?->prenom ?? 'Participant';
        $participantNom    = $participantUser->participant?->nom ?? '';

        // Notification au participant : invitation à compléter son formulaire
        Message::create([
            'content' => json_encode([
                'type'               => 'membership_invitation_to_complete',
                'recipient_user_id'  => $participantUser->id,
                'invitation_id'      => $invitation->id,
                'admin_prenom'       => $adminCreateur->participant?->prenom,
                'admin_nom'          => $adminCreateur->participant?->nom,
                'commentaire_admin'  => $invitation->commentaire_admin,
            ]),
        ]);

        // Notification aux autres admins : information sur l'invitation envoyée
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->where('id', '!=', $adminCreateur->id)->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type'               => 'membership_invitation_info',
                    'recipient_user_id'  => $admin->id,
                    'invitation_id'      => $invitation->id,
                    'participant_prenom' => $participantPrenom,
                    'participant_nom'    => $participantNom,
                    'participant_email'  => $participantUser->email,
                    'admin_prenom'       => $adminCreateur->participant?->prenom,
                    'admin_nom'          => $adminCreateur->participant?->nom,
                ]),
            ]);
        }
    }

    /**
     * Notifie tous les administrateurs qu'un participant a soumis son formulaire membership.
     * @author Ngoie Steven
     * @param  FormulaireMembership $demande         Formulaire soumis.
     * @param  User                 $participantUser Utilisateur ayant soumis le formulaire.
     * @return void
     */
    private function notifyAdminsMembershipFormCompleted(FormulaireMembership $demande, User $participantUser): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type'             => 'new_membership_request',
                    'recipient_user_id'=> $admin->id,
                    'demande_id'       => $demande->id,
                    // Fallback sur les données du formulaire si le profil participant est incomplet
                    'prenom'           => $participantUser->participant?->prenom ?? $demande->prenom,
                    'nom'              => $participantUser->participant?->nom ?? $demande->nom,
                    'email'            => $participantUser->email,
                ]),
            ]);
        }
    }

    /**
     * Notifie les autres administrateurs et le participant lorsqu'une invitation est annulée.
     * Deux types de notifications sont créés : une pour les admins et une pour le participant.
     * @author Ngoie Steven
     * @param  InvitationMembership $invitation      Invitation annulée.
     * @param  User                 $adminActeur     Admin ayant effectué l'annulation.
     * @param  User                 $participantUser Participant concerné par l'annulation.
     * @return void
     */
    private function notifyMembershipInvitationCancelled(InvitationMembership $invitation, User $adminActeur, User $participantUser): void
    {
        // Notification aux autres admins
        $admins = User::whereHas('roles', function ($query) {
            $query->where('type', 'Administrateur');
        })->where('id', '!=', $adminActeur->id)->get();

        foreach ($admins as $admin) {
            Message::create([
                'content' => json_encode([
                    'type'               => 'membership_invitation_cancelled_info',
                    'recipient_user_id'  => $admin->id,
                    'invitation_id'      => $invitation->id,
                    'participant_prenom' => $participantUser->participant?->prenom,
                    'participant_nom'    => $participantUser->participant?->nom,
                    'admin_prenom'       => $adminActeur->participant?->prenom,
                    'admin_nom'          => $adminActeur->participant?->nom,
                ]),
            ]);
        }

        // Notification au participant : son invitation a été annulée par un admin
        Message::create([
            'content' => json_encode([
                'type'              => 'membership_invitation_cancelled_participant',
                'recipient_user_id' => $participantUser->id,
                'invitation_id'     => $invitation->id,
                'admin_prenom'      => $adminActeur->participant?->prenom,
                'admin_nom'         => $adminActeur->participant?->nom,
            ]),
        ]);
    }

    /**
     * Supprime toutes les notifications d'un type donné liées à une demande membership spécifique.
     * Utilisé pour nettoyer les notifications obsolètes après une décision admin.
     * @author Ngoie Steven
     * @param  int    $demandeId Identifiant de la demande membership.
     * @param  string $type      Type de notification à supprimer (ex: 'new_membership_request').
     * @return void
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