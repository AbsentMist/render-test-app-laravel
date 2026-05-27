<?php

/**
 * @fileoverview SousCategorieController.php
 * @description Contrôleur gérant les sous-catégories de courses (ex: Senior H, Junior F).
 *              Fonctionne de manière identique à CategorieController :
 *              vue admin (modèles réutilisables) et vue participant (sous-catégories
 *              assignées à une course spécifique).
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\SousCategorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SousCategorieController extends Controller
{
    /**
     * Retourne toutes les sous-catégories marquées comme modèles réutilisables (vue admin).
     * @author Neris Alessandro
     * @return JsonResponse Liste des sous-catégories modèles.
     */
    public function indexAdmin(): JsonResponse
    {
        // Seules les sous-catégories marquées modele=true sont exposées à l'admin
        $sousCategories = SousCategorie::where('modele', true)->get();
        return response()->json($sousCategories);
    }

    /**
     * Retourne les sous-catégories assignées à une course spécifique (vue participant).
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Liste des sous-catégories ou 404 si aucune n'est assignée.
     */
    public function indexParticipant($id_course): JsonResponse
    {
        $sousCategorie = SousCategorie::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id', $id_course);
        })->get();

        if ($sousCategorie->isEmpty()) {
            return response()->json(['message' => 'Aucune sous categorie disponible pour cette course.'], 404);
        }

        return response()->json($sousCategorie, 200);
    }

    /**
     * Crée une nouvelle sous-catégorie (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Données de la sous-catégorie (nom, modele).
     * @return JsonResponse Sous-catégorie créée (201).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom'    => 'required|string|max:100',
            'modele' => 'boolean',
        ]);

        $sousCategorie = SousCategorie::create($validatedData);

        return response()->json([
            'message'       => 'Sous-catégorie créée avec succès.',
            'sousCategorie' => $sousCategorie
        ], 201);
    }

    /**
     * Retourne le détail d'une sous-catégorie spécifique.
     * @author Neris Alessandro
     * @param  int $id Identifiant de la sous-catégorie.
     * @return JsonResponse Sous-catégorie ou 404.
     */
    public function show($id): JsonResponse
    {
        $sousCategorie = SousCategorie::findOrFail($id);
        return response()->json($sousCategorie);
    }

    /**
     * Met à jour une sous-catégorie existante (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant de la sous-catégorie.
     * @return JsonResponse Sous-catégorie mise à jour.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $sousCategorie = SousCategorie::findOrFail($id);

        $validatedData = $request->validate([
            'nom'    => 'sometimes|string|max:100',
            'modele' => 'boolean',
        ]);

        $sousCategorie->update($validatedData);

        return response()->json([
            'message'       => 'Sous-catégorie mise à jour avec succès.',
            'sousCategorie' => $sousCategorie
        ]);
    }

    /**
     * Supprime définitivement une sous-catégorie (vue admin).
     * @author Neris Alessandro
     * @param  int $id Identifiant de la sous-catégorie à supprimer.
     * @return JsonResponse Message de confirmation.
     */
    public function destroy($id): JsonResponse
    {
        $sousCategorie = SousCategorie::findOrFail($id);
        $sousCategorie->delete();

        return response()->json(['message' => 'Sous-catégorie supprimée avec succès.']);
    }
}