<?php
namespace App\Http\Controllers;
use App\Models\SousCategorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class SousCategorieController extends Controller
{
    /**
     * GET : Liste toutes les sous-catégories
     */
    public function indexAdmin(): JsonResponse
    {
        $sousCategories = SousCategorie::where('modele', true)->get();
        return response()->json($sousCategories);
    }

     public function indexParticipant($id_course): JsonResponse
    {
        
        $sousCategorie = SousCategorie::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })->get();

        if ($sousCategorie->isEmpty()) {
            return response()->json(['message' => 'Aucune sous categorie disponible pour cette course.'], 404);
        }

        return response()->json($sousCategorie, 200);
    }

    /**
     * POST : Créer une nouvelle sous-catégorie (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:100',
            'modele' => 'boolean',
        ]);

        $sousCategorie = SousCategorie::create($validatedData);

        return response()->json([
            'message' => 'Sous-catégorie créée avec succès.',
            'sousCategorie' => $sousCategorie
        ], 201);
    }

    /**
     * GET : Voir une sous-catégorie spécifique
     */
    public function show($id): JsonResponse
    {
        $sousCategorie = SousCategorie::findOrFail($id);
        return response()->json($sousCategorie);
    }

    /**
     * PUT/PATCH : Modifier une sous-catégorie (Admin)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $sousCategorie = SousCategorie::findOrFail($id);

        $validatedData = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'modele' => 'boolean',
        ]);

        $sousCategorie->update($validatedData);

        return response()->json([
            'message' => 'Sous-catégorie mise à jour avec succès.',
            'sousCategorie' => $sousCategorie
        ]);
    }

    /**
     * DELETE : Supprimer une sous-catégorie (Admin)
     */
    public function destroy($id): JsonResponse
    {
        $sousCategorie = SousCategorie::findOrFail($id);
        $sousCategorie->delete();

        return response()->json([
            'message' => 'Sous-catégorie supprimée avec succès.'
        ]);
    }
}