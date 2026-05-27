<?php

/**
 * @fileoverview TemplateController.php
 * @description Contrôleur gérant les templates de contenu réutilisables (vue admin).
 *              Les templates permettent de pré-remplir des champs texte complexes
 *              (ex: description de course, règlement) pour éviter les ressaisies.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
    /**
     * Retourne tous les templates disponibles (vue admin).
     * @author Neris Alessandro
     * @return JsonResponse Liste complète des templates.
     */
    public function indexAdmin(): JsonResponse
    {
        $templates = Template::all();
        return response()->json($templates);
    }

    /**
     * Crée un nouveau template de contenu (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Données du template (nom optionnel, contenu requis).
     * @return JsonResponse Template créé (201).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom'     => 'nullable|string|max:255',
            'contenu' => 'required|string',
        ]);

        $template = Template::create($validatedData);

        return response()->json([
            'message'  => 'Modèle de template créé avec succès.',
            'template' => $template
        ], 201);
    }

    /**
     * Retourne le détail d'un template spécifique.
     * @author Neris Alessandro
     * @param  int $id Identifiant du template.
     * @return JsonResponse Template ou 404.
     */
    public function show($id): JsonResponse
    {
        $template = Template::findOrFail($id);
        return response()->json($template);
    }

    /**
     * Met à jour un template existant (vue admin).
     * Tous les champs sont optionnels.
     * @author Neris Alessandro
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant du template.
     * @return JsonResponse Template mis à jour.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $template = Template::findOrFail($id);

        $validatedData = $request->validate([
            'nom'     => 'sometimes|string|max:255',
            'contenu' => 'sometimes|string',
        ]);

        $template->update($validatedData);

        return response()->json([
            'message'  => 'Modèle de template mis à jour avec succès.',
            'template' => $template
        ]);
    }

    /**
     * Supprime définitivement un template (vue admin).
     * @author Neris Alessandro
     * @param  int $id Identifiant du template à supprimer.
     * @return JsonResponse Message de confirmation.
     */
    public function destroy($id): JsonResponse
    {
        $template = Template::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Modèle de template supprimé avec succès.']);
    }
}