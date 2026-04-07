<?php
namespace App\Http\Controllers;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class CategorieController extends Controller
{
    /**
     * GET : Liste toutes les catégories (Admin)
     */
    public function indexAdmin(): JsonResponse
    {
        $categories = Categorie::where('modele', true)->get();
        return response()->json($categories);
    }

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
     * POST : Créer une nouvelle catégorie (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:100',
            'modele' => 'boolean'
        ]);

        $categorie = Categorie::create($validatedData);

        return response()->json([
            'message' => 'Catégorie créée avec succès.',
            'categorie' => $categorie
        ], 201);
    }

    /**
     * GET : Voir une catégorie spécifique
     */
    public function show($id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);
        return response()->json($categorie);
    }

    /**
     * PUT/PATCH : Modifier une catégorie (Admin)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);

        $validatedData = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'modele' => 'boolean',
        ]);

        $categorie->update($validatedData);

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès.',
            'categorie' => $categorie
        ]);
    }

    /**
     * DELETE : Supprimer une catégorie (Admin)
     */
    public function destroy($id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès.'
        ]);
    }
}