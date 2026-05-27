<?php

/**
 * @fileoverview AvertissementController.php
 * @description Contrôleur gérant les avertissements affichés aux participants lors de l'inscription.
 *              Un avertissement doit être explicitement accepté avant de valider une inscription
 *              si la course l'exige (is_avertissement = true).
 *              Le flag `modele` distingue les avertissements réutilisables des avertissements
 *              créés spécifiquement pour une course.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\Avertissement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AvertissementController extends Controller
{
    /**
     * Retourne tous les avertissements marqués comme modèles réutilisables (vue admin).
     * Utilisé lors de la configuration d'une course pour choisir un avertissement existant.
     * @author Neris Alessandro
     * @return JsonResponse Liste des avertissements modèles.
     */
    public function indexAdmin(): JsonResponse
    {
        // Seuls les avertissements marqués modele=true sont exposés à l'admin
        $avertissements = Avertissement::where('modele', true)->get();
        return response()->json($avertissements);
    }

    /**
     * Retourne les avertissements assignés à une course spécifique (vue participant).
     * Appelé lors du chargement du formulaire d'inscription pour afficher l'avertissement à accepter.
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Avertissements de la course ou 404 si aucun.
     */
    public function indexParticipant($id_course): JsonResponse
    {
        $avertissement = Avertissement::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })->get();

        if ($avertissement->isEmpty()) {
            return response()->json(['message' => 'Aucun avertissement disponible pour cette course.'], 404);
        }

        return response()->json($avertissement, 200);
    }

    /**
     * Crée un nouveau modèle d'avertissement (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Données de l'avertissement (titre, contenu, modele).
     * @return JsonResponse Avertissement créé (201).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'titre'   => 'nullable|string|max:100',
            'contenu' => 'required|string',
            'modele'  => 'boolean',
        ]);

        $avertissement = Avertissement::create($validatedData);

        return response()->json([
            'message'       => 'Modèle d\'avertissement créé avec succès.',
            'avertissement' => $avertissement
        ], 201);
    }

    /**
     * Retourne le détail d'un avertissement spécifique.
     * @author Neris Alessandro
     * @param  int $id Identifiant de l'avertissement.
     * @return JsonResponse Avertissement ou 404.
     */
    public function show($id): JsonResponse
    {
        $avertissement = Avertissement::findOrFail($id);
        return response()->json($avertissement);
    }

    /**
     * Met à jour un modèle d'avertissement existant (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant de l'avertissement.
     * @return JsonResponse Avertissement mis à jour.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $avertissement = Avertissement::findOrFail($id);

        $validatedData = $request->validate([
            'titre'   => 'sometimes|string|max:100',
            'contenu' => 'sometimes|string',
            'modele'  => 'boolean',
        ]);

        $avertissement->update($validatedData);

        return response()->json([
            'message'       => 'Modèle d\'avertissement mis à jour avec succès.',
            'avertissement' => $avertissement
        ]);
    }

    /**
     * Supprime définitivement un avertissement (vue admin).
     * @author Neris Alessandro
     * @param  int $id Identifiant de l'avertissement à supprimer.
     * @return JsonResponse Message de confirmation.
     */
    public function destroy($id): JsonResponse
    {
        $avertissement = Avertissement::findOrFail($id);
        $avertissement->delete();

        return response()->json(['message' => 'Modèle d\'avertissement supprimé avec succès.']);
    }
}