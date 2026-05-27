<?php

/**
 * @fileoverview CategorieController.php
 * @description Contrôleur gérant les catégories de courses (ex: trail, route, marche).
 *              Deux modes d'accès : vue admin (modèles réutilisables) et vue participant
 *              (catégories assignées à une course spécifique).
 *              Le flag `modele` distingue les catégories réutilisables des catégories
 *              créées spécifiquement pour une course.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategorieController extends Controller
{
    /**
     * Retourne toutes les catégories marquées comme modèles réutilisables (vue admin).
     * Ces modèles sont disponibles lors de la création ou modification d'une course.
     * @author Neris Alessandro
     * @return JsonResponse Liste des catégories modèles.
     */
    public function indexAdmin(): JsonResponse
    {
        // Seules les catégories marquées modele=true sont exposées à l'admin
        $categories = Categorie::where('modele', true)->get();
        return response()->json($categories);
    }

    /**
     * Retourne les catégories assignées à une course spécifique (vue participant).
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Liste des catégories ou 404 si aucune n'est assignée.
     */
    public function indexParticipant($id_course): JsonResponse
    {
        $categorie = Categorie::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id', $id_course);
        })->get();

        if ($categorie->isEmpty()) {
            return response()->json(['message' => 'Aucune categorie disponible pour cette course.'], 404);
        }

        return response()->json($categorie, 200);
    }

    /**
     * Crée une nouvelle catégorie (vue admin).
     * Le flag `modele` détermine si la catégorie sera réutilisable dans d'autres courses.
     * @author Neris Alessandro
     * @param  Request $request Données de la catégorie (nom, modele).
     * @return JsonResponse Catégorie créée (201).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom'    => 'required|string|max:100',
            'modele' => 'boolean',
        ]);

        $categorie = Categorie::create($validatedData);

        return response()->json([
            'message'   => 'Catégorie créée avec succès.',
            'categorie' => $categorie
        ], 201);
    }

    /**
     * Retourne le détail d'une catégorie spécifique.
     * @author Neris Alessandro
     * @param  int $id Identifiant de la catégorie.
     * @return JsonResponse Catégorie ou 404.
     */
    public function show($id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);
        return response()->json($categorie);
    }

    /**
     * Met à jour une catégorie existante (vue admin).
     * Tous les champs sont optionnels.
     * @author Neris Alessandro
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant de la catégorie.
     * @return JsonResponse Catégorie mise à jour.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);

        $validatedData = $request->validate([
            'nom'    => 'sometimes|string|max:100',
            'modele' => 'boolean',
        ]);

        $categorie->update($validatedData);

        return response()->json([
            'message'   => 'Catégorie mise à jour avec succès.',
            'categorie' => $categorie
        ]);
    }

    /**
     * Supprime définitivement une catégorie (vue admin).
     * @author Neris Alessandro
     * @param  int $id Identifiant de la catégorie à supprimer.
     * @return JsonResponse Message de confirmation.
     */
    public function destroy($id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();

        return response()->json(['message' => 'Catégorie supprimée avec succès.']);
    }
}