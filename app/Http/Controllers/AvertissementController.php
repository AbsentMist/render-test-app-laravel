<?php

namespace App\Http\Controllers;

use App\Models\Avertissement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AvertissementController extends Controller
{
    /**
     * GET : Liste tous les avertissements (Admin)
     */
    public function indexAdmin(): JsonResponse
    {
        // On récupère tous les modèles d'avertissements
        $avertissements = Avertissement::where('modele', true)->get();

        return response()->json($avertissements);
    }

    public function indexParticipant($id_course): JsonResponse
    {
        
        $avertissement = Avertissement::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        });

        if ($avertissement->isEmpty()) {
            return response()->json(['message' => 'Aucun avertissement disponible pour cette course.'], 404);
        }

        return response()->json($avertissement, 200);
    }

    /**
     * POST : Créer un nouvel avertissement (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'titre'   => 'nullable|string|max:100',
            'contenu' => 'required|string',
            'modele'      => 'boolean',
        ]);

        $avertissement = Avertissement::create($validatedData);

        return response()->json([
            'message' => 'Modèle d\'avertissement créé avec succès.',
            'avertissement' => $avertissement
        ], 201);
    }

    /**
     * GET : Voir un avertissement spécifique
     */
    public function show($id): JsonResponse
    {
        $avertissement = Avertissement::findOrFail($id);

        return response()->json($avertissement);
    }

    /**
     * PUT/PATCH : Modifier un avertissement (Admin)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $avertissement = Avertissement::findOrFail($id);

        $validatedData = $request->validate([
            'titre'   => 'sometimes|string|max:100',
            'contenu' => 'sometimes|string',
            'modele'      => 'boolean',
        ]);

        $avertissement->update($validatedData);

        return response()->json([
            'message' => 'Modèle d\'avertissement mis à jour avec succès.',
            'avertissement' => $avertissement
        ]);
    }

    /**
     * DELETE : Supprimer un avertissement (Admin)
     */
    public function destroy($id): JsonResponse
    {
        $avertissement = Avertissement::findOrFail($id);
        $avertissement->delete();

        return response()->json([
            'message' => 'Modèle d\'avertissement supprimé avec succès.'
        ]);
    }
}