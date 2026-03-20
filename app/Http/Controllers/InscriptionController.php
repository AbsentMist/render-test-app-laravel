<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Course;
use App\Models\Dossard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InscriptionController extends Controller
{
    //GET (ADMIN)
    public function indexAdmin()
    {
        //Vérifie si c'est un admin
        $user = Auth::user();
        if (!$user->roles()->where('type', 'Administrateur')->exists()) {
            return response()->json(['message' => 'Accès non autorisé. Réservé aux administrateurs.'], 403);
        }

        $inscriptions = Inscription::with(['course.evenement', 'participant', 'dossard', 'groupe'])->get();
        
        return response()->json($inscriptions);
    }

    //GET (PARTICIPANT)
    public function indexParticipant()
    {
        $user = Auth::user();
        
        //Récupération de l'ID du participant
        $idParticipant = $user->participant->id;
        
        //Renvoie uniquement les inscriptions du participant
        $inscriptions = Inscription::with(['course.evenement', 'dossard', 'groupe', 'participant'])
            ->where('id_participant', $idParticipant)
            ->get();

        return response()->json($inscriptions);
    }

    //POST (PARTICIPANT)
    public function store(Request $request)
    {
        // Validation des données selon la DB
        $validatedData = $request->validate([
            'id_course' => 'required|exists:Course,id',
            'id_participant' => 'nullable|exists:Participant,id',
            'id_groupe' => 'nullable|exists:Groupe,id',
            'id_document' => 'nullable|exists:Document,id',
            'code_participant' => 'nullable|string|unique:Inscription,code_participant',
            'avertissement_valide' => 'sometimes|boolean',
            //Ajout du champ "en attente" pour les inscriptions relais / entreprises
            'status_paiement'     => 'sometimes|in:Validé,Annulé,En attente', 
        ]);

        $idParticipantConnecte = Auth::user()->participant->id;
        $idParticipant = $request->id_participant ?? $idParticipantConnecte;
        $course = Course::findOrFail($validatedData['id_course']);

        // Lorsque l'inscription est en groupe, tout le monde commence "En attente" jusqu'à ce que chaque membre accepte l'invitation 
        $statutInscription = !empty($validatedData['id_groupe']) ? 'En attente' : 'Validé';
        $statutPaiementFinal = $validatedData['status_paiement'] ?? $statutInscription;

        // Gestion erreur si la course à un avertissement obligatoire
        if ($course->is_avertissement == 1 && empty($validatedData['avertissement_valide'])) {
            return response()->json([
                'message' => 'Vous devez accepter les conditions/avertissements liés à cette course pour vous inscrire.'
            ], 422);
        }

        // Vérifie si le participant est déjà inscrit pour éviter les doublons
        $inscriptionExistante = Inscription::where('id_participant', $idParticipant)
            ->where('id_course', $course->id)
            ->first();

        if ($inscriptionExistante) {
            //Gestion erreur, si inscription déjà existante et que le statut de paiement est "En attente" ou "Validé", on bloque la réinscription
            if (in_array($inscriptionExistante->status_paiement, ['En attente', 'Validé'])) {
                return response()->json([
                    'message' => 'Vous êtes déjà inscrit à cette course (ou votre inscription est en attente de paiement).'
                ], 409);
            }

            //Réinscription du participant si l'inscription précédente avait été annulée 
            if ($inscriptionExistante->status_paiement === 'Annulé') {
                $inscriptionExistante->update([
                    'id_groupe' => $validatedData['id_groupe'] ?? null,
                    'id_document' => $validatedData['id_document'] ?? null,
                    'code_participant' => $validatedData['code_participant'] ?? null,
                    'tarif' => $course->tarif,
                    'status_paiement' => $statutPaiementFinal, // Application du statut dynamique
                    'montant_rabais' => 0,
                    'avertissement_valide' => $validatedData['avertissement_valide'] ?? false,
                ]);

                // On renvoie 200 (OK) au lieu de 201 car modification
                return response()->json($inscriptionExistante->load(['course', 'dossard']), 200);
            }
        }

        // Création de l'inscription (s'il n'y avait aucun historique)
        $inscription = Inscription::create([
            'id_participant' => $idParticipant,
            'id_course' => $course->id,
            'id_groupe' => $validatedData['id_groupe'] ?? null,
            'id_document' => $validatedData['id_document'] ?? null,
            'code_participant' => $validatedData['code_participant'] ?? null,
            'tarif' => $course->tarif,
            'status_paiement' => $statutPaiementFinal, // Application du statut dynamique
            'montant_rabais' => 0,
            'avertissement_valide' => $validatedData['avertissement_valide'] ?? false,
        ]);

        // ANTICIPATION DOSSARD (À développer plus tard)
        // Si la course gère les dossards automatiquement, on pourrait le créer ici.
        // if ($course->is_dossard == 1 && $configurationOrganisateur == 'auto') {
        //     Dossard::create([...]);
        // }

        return response()->json($inscription->load(['course', 'dossard']), 201);
    }

    //GET (PARTICIPANT & ADMIN)
    public function show($id)
    {
        $inscription = Inscription::with(['course.evenement', 'participant', 'dossard', 'groupe'])->findOrFail($id);
        
        $user = Auth::user();
        $isAdmin = $user->roles()->where('type', 'Administrateur')->exists();
        
        if (!$isAdmin && $inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return response()->json($inscription);
    }

   //PUT (ADMIN)
    public function updateAdmin(Request $request, $id)
    {
        $inscription = Inscription::findOrFail($id);

        // Protection métier : Un admin ne doit pas modifier le statut d'une inscription annulée
        if ($inscription->status_paiement === 'Annulé' && $request->has('status_paiement') && $request->status_paiement !== 'Annulé') {
            return response()->json([
                'message' => 'Impossible de modifier le statut de paiement d\'une inscription annulée.'
            ], 400);
        }

        $validatedData = $request->validate([
            'status_paiement' => 'sometimes|in:Validé,En attente,Annulé',
            'tarif' => 'sometimes|numeric',
            'montant_rabais' => 'sometimes|numeric',
            'avertissement_valide' => 'sometimes|boolean',
            'id_document' => 'sometimes|nullable|exists:Document,id',
            'id_groupe' => 'sometimes|nullable|exists:Groupe,id',
            'id_course' => 'sometimes|exists:Course,id', 
        ]);

        $inscription->update($validatedData);

        return response()->json($inscription->load(['course', 'participant', 'dossard']));
    }

    //DELETE (ADMIN)
    public function destroyAdmin($id)
    {
        $inscription = Inscription::findOrFail($id);
        
        $inscription->delete();

        return response()->json(['message' => 'Inscription supprimée avec succès.']);
    }

    //PUT (PARTICIPANT)
    public function updateParticipant(Request $request, $id)
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id);

        // Gestion erreur : un participant ne peut modifier que sa propre inscription
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        /* * =================================================================================
         * GESTION UPGRADE / DOWNGRADE (Tâches 7.2 & 7.3 à faire ici)
         * =================================================================================
         */

        // Modification des informations simples (Tâche 7.1)
        $validatedData = $request->validate([
            'id_groupe' => 'sometimes|nullable|exists:Groupe,id',
            'id_document' => 'sometimes|nullable|exists:Document,id',
            'code_participant' => 'sometimes|nullable|string|unique:Inscription,code_participant,' . $inscription->id, 
        ]);

        $inscription->update($validatedData);

        return response()->json($inscription->load(['course', 'dossard', 'groupe']));
    }

    //DELETE (PARTICIPANT) : Modifie le statut de paiement à "Annulé" au lieu de supprimer l'inscription
    public function destroyParticipant($id)
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id);
        
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Gestion erreur sur le statut de paiement
        if ($inscription->status_paiement === 'Validé') {
            return response()->json([
                'message' => 'Impossible d\'annuler une inscription déjà payée. Veuillez contacter l\'organisateur pour toute demande d\'annulation ou de remboursement.'
            ], 400);
        }

        // Changement de statut au lieu d'une suppression en base de données (réservé à l'admin)
        $inscription->update(['status_paiement' => 'Annulé']);

        return response()->json([
            'message' => 'Inscription annulée avec succès.',
            'inscription' => $inscription
        ]);
    }
}