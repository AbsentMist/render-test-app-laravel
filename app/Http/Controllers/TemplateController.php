<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
    /**
     * GET : Liste tous les templates (Admin)
     */
    public function indexAdmin(): JsonResponse
    {
        // On récupère tous les modèles de templates
        $templates = Template::All();

        return response()->json($templates); 
    }
    /**
     * POST : Créer un nouveau template (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom'   => 'nullable|string|max:255',
            'contenu' => 'required|string',
        ]);

        $template = Template::create($validatedData);

        return response()->json([
            'message' => 'Modèle de template créé avec succès.',
            'template' => $template
        ], 201);
    }

    /**
     * GET : Voir un template spécifique
     */
    public function show($id): JsonResponse
    {
        $template = Template::findOrFail($id);

        return response()->json($template);
    }

    /**
     * PUT/PATCH : Modifier un template (Admin)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $template = Template::findOrFail($id);

        $validatedData = $request->validate([
            'nom'   => 'sometimes|string|max:255',
            'contenu' => 'sometimes|string',
        ]);

        $template->update($validatedData);

        return response()->json([
            'message' => 'Modèle de template mis à jour avec succès.',
            'template' => $template
        ]);
    }

    /**
     * DELETE : Supprimer un template (Admin)
     */
    public function destroy($id): JsonResponse
    {
        $template = Template::findOrFail($id);
        $template->delete();

        return response()->json([
            'message' => 'Modèle de template supprimé avec succès.'
        ]);
    }
}