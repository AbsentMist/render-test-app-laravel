<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Dossard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EchangeDossardController extends Controller
{
    /**
     * POST /participant/echange-dossard/initier
     *
     * Initie une demande d'échange de dossard.
     * - Vérifie que l'inscription de A est Validée et possède un dossard
     * - Vérifie que B existe dans le système via son email
     * - Vérifie que B n'est pas déjà inscrit à cette course (statut actif)
     * - Crée une nouvelle inscription pour B en "En attente"
     *
     * Le mail à B est envoyé par Steven (tâche 2,2).
     */
    public function initier(Request $request)
    {
        $validated = $request->validate([
            'id_inscription'  => 'required|exists:Inscription,id',
            'email_destinataire' => 'required|email',
        ]);

        $user = Auth::user();

        // --- Récupération et vérification de l'inscription de A ---
        $inscriptionA = Inscription::with(['course', 'dossard', 'participant'])
            ->findOrFail($validated['id_inscription']);

        // Seul le propriétaire de l'inscription peut initier l'échange
        if ($inscriptionA->id_participant !== $user->participant->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à échanger cette inscription.'
            ], 403);
        }

        // L'inscription doit être Validée (donc payée)
        if ($inscriptionA->status_paiement !== 'Validé') {
            return response()->json([
                'message' => 'Seules les inscriptions validées (payées) peuvent être échangées.'
            ], 422);
        }

        // L'inscription doit avoir un dossard attribué
        if (!$inscriptionA->dossard) {
            return response()->json([
                'message' => 'Aucun dossard n\'est associé à cette inscription.'
            ], 422);
        }

        // Vérifier qu'un échange n'est pas déjà en cours pour cette inscription
        $echangeEnCours = Inscription::where('id_ancienne_inscription', $inscriptionA->id)
            ->where('status_paiement', 'En attente')
            ->exists();

        if ($echangeEnCours) {
            return response()->json([
                'message' => 'Un échange est déjà en cours pour cette inscription. Attendez la réponse du destinataire.'
            ], 409);
        }

        // --- Vérification du destinataire B ---
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

        // Empêcher d'envoyer l'échange à soi-même
        if ($userB->id === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas vous envoyer un échange à vous-même.'
            ], 422);
        }

        // Vérifier que B n'est pas déjà actif sur cette course
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
        $inscriptionB = Inscription::create([
            'id_participant'          => $userB->participant->id,
            'id_course'               => $inscriptionA->id_course,
            'id_ancienne_inscription' => $inscriptionA->id,
            'tarif'                   => 0, // A a déjà payé
            'status_paiement'         => 'En attente',
            'date_paiement'           => now(),
            'avertissement_valide'    => $inscriptionA->avertissement_valide,
            'montant_rabais'          => 0,
        ]);

        // Chargement des relations pour la réponse et pour le mail (tâche 2,2 - Steven)
        $inscriptionB->load([
            'participant.user',
            'course.evenement',
            'ancienneInscription.participant.user',
            'ancienneInscription.dossard',
        ]);

        // TODO (Steven - tâche 2,2) : envoyer le mail de demande d'échange à B
        // Mail::to($userB->email)->send(new DemandeEchangeDossardMail($inscriptionB));

        return response()->json([
            'message'      => 'Demande d\'échange envoyée avec succès.',
            'inscription'  => $inscriptionB,
        ], 201);
    }

    /**
     * POST /participant/echange-dossard/{id}/accepter
     *
     * B accepte l'échange :
     * - Son inscription passe à 'Validé'
     * - Le dossard de A est transféré vers B
     * - L'inscription de A passe à 'Échangé'
     */
    public function accepter($id)
    {
        $user = Auth::user();

        $inscriptionB = Inscription::with([
            'ancienneInscription.dossard',
            'ancienneInscription.participant',
        ])->findOrFail($id);

        // Seul B peut accepter
        if ($inscriptionB->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // L'inscription doit être en attente
        if ($inscriptionB->status_paiement !== 'En attente') {
            return response()->json(['message' => 'Cette demande d\'échange n\'est plus en attente.'], 422);
        }

        $inscriptionA = $inscriptionB->ancienneInscription;

        if (!$inscriptionA) {
            return response()->json(['message' => 'Inscription source introuvable.'], 404);
        }

        // Transfert du dossard : on pointe le dossard de A vers l'inscription de B
        if ($inscriptionA->dossard) {
            $inscriptionA->dossard->update(['id_inscription' => $inscriptionB->id]);
        }

        // Mise à jour des statuts
        $inscriptionB->update(['status_paiement' => 'Validé']);
        $inscriptionA->update(['status_paiement' => 'Échangé']);

        // TODO (Steven - tâche 2,3) : envoyer mail de confirmation à A et B

        return response()->json([
            'message' => 'Échange accepté avec succès.',
        ], 200);
    }

    /**
     * POST /participant/echange-dossard/{id}/refuser
     *
     * B refuse l'échange :
     * - L'inscription de B est supprimée
     * - L'inscription de A reste inchangée (toujours Validée)
     */
    public function refuser($id)
    {
        $user = Auth::user();

        $inscriptionB = Inscription::findOrFail($id);

        // Seul B peut refuser
        if ($inscriptionB->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($inscriptionB->status_paiement !== 'En attente') {
            return response()->json(['message' => 'Cette demande d\'échange n\'est plus en attente.'], 422);
        }

        // TODO (Steven - tâche 2,3) : envoyer mail de refus à A

        $inscriptionB->delete();

        return response()->json([
            'message' => 'Échange refusé. L\'inscription originale reste inchangée.',
        ], 200);
    }

    /**
     * GET /participant/echange-dossard/mes-demandes-recues
     *
     * Retourne les demandes d'échange en attente reçues par le participant connecté.
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

        return response()->json($demandes);
    }

    /**
     * GET /participant/echange-dossard/mes-demandes-envoyees
     *
     * Retourne les demandes d'échange envoyées par le participant connecté et toujours en attente.
     */
    public function mesDemandesEnvoyees()
    {
        $idParticipant = Auth::user()->participant->id;

        // On cherche les inscriptions en attente dont l'inscription source appartient au participant connecté
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

        return response()->json($demandes);
    }

    /**
     * DELETE /participant/echange-dossard/{id}/annuler
     *
     * A annule sa demande d'échange en attente.
     * - L'inscription de B (En attente) est supprimée
     * - L'inscription de A reste inchangée (toujours Validée)
     */
    public function annuler($id)
    {
        $user = Auth::user();

        $inscriptionB = Inscription::with('ancienneInscription')->findOrFail($id);

        // Vérifier que c'est bien A (propriétaire de l'inscription source) qui annule
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
}