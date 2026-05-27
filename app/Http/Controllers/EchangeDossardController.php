<?php

/**
 * @fileoverview EchangeDossardController.php
 * @description Contrôleur gérant le flux complet d'échange de dossard entre deux participants :
 *              initiation de la demande, acceptation, refus et annulation.
 *              Le participant A cède son dossard à B ; B peut accepter ou refuser.
 *              Les notifications par message interne et par email sont envoyées à chaque étape.
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Dossard;
use App\Models\Groupe;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EchangeDossardController extends Controller
{
    /**
     * Initie une demande d'échange de dossard de A vers B.
     * Effectue les vérifications suivantes avant de créer la demande :
     *   - A est bien le propriétaire de l'inscription
     *   - L'inscription de A est validée (payée) et possède un dossard
     *   - Aucun échange n'est déjà en cours pour cette inscription
     *   - B existe sur la plateforme et possède un profil participant
     *   - A et B sont des personnes différentes
     *   - B n'est pas déjà inscrit ou en attente sur cette course
     * Crée une inscription "En attente" pour B et lui envoie un email de notification.
     * @author Guillermet Jean-Daniel
     * @author Ngoie Steven (envoi de l'email de demande à B)
     * @param  Request $request Doit contenir `id_inscription` et `email_destinataire`.
     * @return \Illuminate\Http\JsonResponse Inscription de B créée (201) ou erreur métier.
     */
    public function initier(Request $request)
    {
        $validated = $request->validate([
            'id_inscription'    => 'required|exists:Inscription,id',
            'email_destinataire'=> 'required|email',
        ]);

        $user = Auth::user();

        // --- Vérifications sur l'inscription de A ---
        $inscriptionA = Inscription::with(['course', 'dossard', 'participant'])
            ->findOrFail($validated['id_inscription']);

        // Seul le propriétaire de l'inscription peut initier l'échange
        if ($inscriptionA->id_participant !== $user->participant->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à échanger cette inscription.'
            ], 403);
        }

        // L'inscription doit être validée (donc payée) pour pouvoir être échangée
        if ($inscriptionA->status_paiement !== 'Validé') {
            return response()->json([
                'message' => 'Seules les inscriptions validées (payées) peuvent être échangées.'
            ], 422);
        }

        // Un dossard doit être associé à l'inscription pour qu'il puisse être transféré
        if (!$inscriptionA->dossard) {
            return response()->json([
                'message' => 'Aucun dossard n\'est associé à cette inscription.'
            ], 422);
        }

        // Vérifie qu'aucun échange n'est déjà en attente pour cette inscription
        $echangeEnCours = Inscription::where('id_ancienne_inscription', $inscriptionA->id)
            ->where('status_paiement', 'En attente')
            ->exists();

        if ($echangeEnCours) {
            return response()->json([
                'message' => 'Un échange est déjà en cours pour cette inscription. Attendez la réponse du destinataire.'
            ], 409);
        }

        // --- Vérifications sur le destinataire B ---
        $userB = User::where('email', $validated['email_destinataire'])->first();

        if (!$userB) {
            return response()->json([
                'message' => 'Aucun compte n\'existe avec cette adresse email. Le destinataire doit être inscrit sur la plateforme.'
            ], 404);
        }

        if (!$userB->participant) {
            return response()->json([
                'message' => 'Ce compte ne possède pas encore de profil participant.'
            ], 422);
        }

        // Un participant ne peut pas s'envoyer un échange à lui-même
        if ($userB->id === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas vous envoyer un échange à vous-même.'
            ], 422);
        }

        // Vérifie que B n'est pas déjà actif sur cette course (validé ou en attente)
        $inscriptionBExistante = Inscription::where('id_participant', $userB->participant->id)
            ->where('id_course', $inscriptionA->id_course)
            ->whereIn('status_paiement', ['Validé', 'En attente'])
            ->exists();

        if ($inscriptionBExistante) {
            return response()->json([
                'message' => 'Ce participant est déjà inscrit (ou a une inscription en attente) pour cette course.'
            ], 409);
        }

        // --- Création de l'inscription "En attente" pour B ---
        // Le tarif est 0 car A a déjà payé ; le dossard sera transféré lors de l'acceptation
        $inscriptionB = Inscription::create([
            'id_participant'          => $userB->participant->id,
            'id_course'               => $inscriptionA->id_course,
            'id_ancienne_inscription' => $inscriptionA->id,
            'tarif'                   => 0,
            'status_paiement'         => 'En attente',
            'date_paiement'           => now(),
            'avertissement_valide'    => $inscriptionA->avertissement_valide,
            'montant_rabais'          => 0,
        ]);

        // Charge les relations nécessaires à la sérialisation et à l'email
        $inscriptionB->load([
            'participant.user',
            'course.evenement',
            'ancienneInscription.participant.user',
            'ancienneInscription.dossard',
        ]);

        $this->prepareInscriptionForJson($inscriptionB);

        // Envoie l'email de demande d'échange à B (non-bloquant)
        try {
            Mail::to($userB->email)->send(new \App\Mail\DemandeEchangeDossardMail($inscriptionB));
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi d'email d'échange de dossard : " . $e->getMessage());
        }

        return response()->json([
            'message'     => 'Demande d\'échange envoyée avec succès.',
            'inscription' => $inscriptionB,
        ], 201);
    }

    /**
     * B accepte la demande d'échange.
     * Transfère le dossard de A vers B, passe l'inscription de B à "Validé"
     * et l'inscription de A à "Validé" également.
     * Si A appartenait à un groupe non-Entreprise, il en est retiré (B le remplace).
     * @author Guillermet Jean-Daniel
     * @param  int $id Identifiant de l'inscription de B (la demande en attente).
     * @return \Illuminate\Http\JsonResponse Confirmation (200) ou erreur métier.
     */
    public function accepter($id)
    {
        $user = Auth::user();

        $inscriptionB = Inscription::with([
            'ancienneInscription.dossard',
            'ancienneInscription.participant',
            'participant.user',
            'course.evenement',
        ])->findOrFail($id);

        // Seul B peut accepter sa propre demande d'échange
        if ($inscriptionB->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($inscriptionB->status_paiement !== 'En attente') {
            return response()->json(['message' => 'Cette demande d\'échange n\'est plus en attente.'], 422);
        }

        $inscriptionA = $inscriptionB->ancienneInscription;

        if (!$inscriptionA) {
            return response()->json(['message' => 'Inscription source introuvable.'], 404);
        }

        // Transfère le dossard de A vers B en mettant à jour la clé étrangère
        if ($inscriptionA->dossard) {
            $inscriptionA->dossard->update(['id_inscription' => $inscriptionB->id]);
        }

        // Les deux inscriptions passent à "Validé" pour conserver la traçabilité
        $inscriptionB->update(['status_paiement' => 'Validé']);
        $inscriptionA->update(['status_paiement' => 'Validé']);

        // Retire A du groupe car B le remplace ; les groupes Entreprise sont partagés et ne changent pas
        $idGroupe = $inscriptionA->id_groupe;
        if ($idGroupe) {
            $groupe = Groupe::find($idGroupe);
            if ($groupe && $groupe->type !== 'Entreprise') {
                $groupe->participants()->detach($inscriptionA->id_participant);
            }
        }

        return response()->json([
            'message' => 'Échange accepté avec succès.',
        ], 200);
    }

    /**
     * B refuse la demande d'échange.
     * Supprime l'inscription de B, laisse l'inscription de A inchangée (toujours Validée)
     * et notifie A par email et par message interne.
     * @author Guillermet Jean-Daniel
     * @author Ngoie Steven (envoi de l'email de refus et création de la notification interne)
     * @param  int $id Identifiant de l'inscription de B (la demande en attente).
     * @return \Illuminate\Http\JsonResponse Confirmation (200) ou erreur métier.
     */
    public function refuser($id)
    {
        $user = Auth::user();

        $inscriptionB = Inscription::findOrFail($id);

        // Seul B peut refuser sa propre demande d'échange
        if ($inscriptionB->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($inscriptionB->status_paiement !== 'En attente') {
            return response()->json(['message' => 'Cette demande d\'échange n\'est plus en attente.'], 422);
        }

        $inscriptionA = $inscriptionB->ancienneInscription;

        if (!$inscriptionA) {
            return response()->json(['message' => 'Inscription source introuvable.'], 404);
        }

        // Envoie un email de refus à A (non-bloquant)
        try {
            Mail::to($inscriptionA->participant->user->email)->send(
                new \App\Mail\RefusEchangeDossardMail($inscriptionA, $inscriptionB)
            );
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi d'email de refus d'échange de dossard : " . $e->getMessage());
        }

        // Crée une notification interne pour informer A du refus
        if ($inscriptionA->participant?->user) {
            Message::create([
                'content' => json_encode([
                    'recipient_user_id' => $inscriptionA->participant->user->id,
                    'sender_user_id'    => $user->id,
                    'type'              => 'exchange_refused',
                    'inscription_a_id'  => $inscriptionA->id,
                    'inscription_b_id'  => $inscriptionB->id,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }

        // Supprime l'inscription de B ; l'inscription de A reste active et inchangée
        $inscriptionB->delete();

        return response()->json([
            'message' => 'Échange refusé. L\'inscription originale reste inchangée.',
        ], 200);
    }

    /**
     * Retourne les demandes d'échange reçues par le participant connecté et toujours en attente.
     * Ajoute un tag d'affichage pour la distinction dans le tableau de bord.
     * @author Guillermet Jean-Daniel
     * @author Ngoie Steven (ajout du tag et normalisation JSON via prepareInscriptionForJson)
     * @return \Illuminate\Http\JsonResponse Liste des demandes reçues en attente.
     */
    public function mesDemandesRecues()
    {
        $idParticipant = Auth::user()->participant->id;

        $demandes = Inscription::with([
            'course.evenement',
            'ancienneInscription.participant.user',
            'ancienneInscription.dossard',
        ])
            ->where('id_participant', $idParticipant)
            ->where('status_paiement', 'En attente')
            ->whereNotNull('id_ancienne_inscription')
            ->get();

        $demandes->each(function (Inscription $inscription) {
            $this->prepareInscriptionForJson($inscription);
            $inscription->setAttribute('tag', 'Demande échange dossard');
        });

        return response()->json($demandes);
    }

    /**
     * Retourne les demandes d'échange envoyées par le participant connecté et toujours en attente.
     * Identifie les demandes envoyées via l'inscription source (ancienneInscription) appartenant au participant.
     * @author Guillermet Jean-Daniel
     * @author Ngoie Steven (ajout du tag et normalisation JSON via prepareInscriptionForJson)
     * @return \Illuminate\Http\JsonResponse Liste des demandes envoyées en attente.
     */
    public function mesDemandesEnvoyees()
    {
        $idParticipant = Auth::user()->participant->id;

        // Cherche les inscriptions en attente dont l'inscription source appartient au participant connecté
        $demandes = Inscription::with([
            'course.evenement',
            'participant.user',
            'ancienneInscription.dossard',
        ])
            ->where('status_paiement', 'En attente')
            ->whereNotNull('id_ancienne_inscription')
            ->whereHas('ancienneInscription', function ($query) use ($idParticipant) {
                $query->where('id_participant', $idParticipant);
            })
            ->get();

        $demandes->each(function (Inscription $inscription) {
            $this->prepareInscriptionForJson($inscription);
            $inscription->setAttribute('tag', 'Demande échange dossard');
        });

        return response()->json($demandes);
    }

    /**
     * A annule sa demande d'échange avant que B n'ait répondu.
     * Supprime l'inscription de B (En attente) ; l'inscription de A reste inchangée.
     * @author Guillermet Jean-Daniel
     * @param  int $id Identifiant de l'inscription de B à supprimer.
     * @return \Illuminate\Http\JsonResponse Confirmation (200) ou erreur métier.
     */
    public function annuler($id)
    {
        $user = Auth::user();

        $inscriptionB = Inscription::with('ancienneInscription')->findOrFail($id);

        // Seul A (propriétaire de l'inscription source) peut annuler la demande
        if (!$inscriptionB->ancienneInscription ||
            $inscriptionB->ancienneInscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($inscriptionB->status_paiement !== 'En attente') {
            return response()->json(['message' => 'Cette demande n\'est plus en attente, elle ne peut pas être annulée.'], 422);
        }

        $inscriptionB->delete();

        return response()->json([
            'message' => 'Demande d\'échange annulée. Votre inscription est toujours active.',
        ], 200);
    }

    /**
     * Prépare une inscription pour la sérialisation JSON.
     * Accède aux propriétés photo du participant et du participant source
     * pour déclencher leur conversion (gérée au niveau du modèle Participant).
     * @author Ngoie Steven
     * @param  Inscription $inscription Inscription à préparer.
     * @return void
     */
    private function prepareInscriptionForJson(Inscription $inscription): void
    {
        // La sérialisation de la photo est gérée au niveau du modèle Participant ;
        // l'accès à la propriété suffit à déclencher le cast
        $inscription->participant?->photo;
        $inscription->ancienneInscription?->participant?->photo;
    }
}