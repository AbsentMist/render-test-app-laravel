<?php

/**
 * @fileoverview OptionQuestionController.php
 * @description Contrôleur gérant les choix de réponses (options) des questions du questionnaire.
 *              Chaque question peut avoir plusieurs options de réponse ; le participant
 *              en choisit une lors de l'inscription.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\OptionQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OptionQuestionController extends Controller
{
    /**
     * Retourne tous les choix de réponses d'une question donnée.
     * @author Neris Alessandro
     * @param  int $id_question Identifiant de la question.
     * @return JsonResponse Liste des choix ou 404 si la question est introuvable.
     */
    public function index($id_question): JsonResponse
    {
        $question = Question::find($id_question);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        return response()->json($question->choix, 200);
    }

    /**
     * Retourne le détail d'un choix de réponse spécifique avec sa question parente.
     * @author Neris Alessandro
     * @param  int $id Identifiant du choix de réponse.
     * @return JsonResponse Choix avec sa question ou 404.
     */
    public function show($id): JsonResponse
    {
        $option = OptionQuestion::with(['question'])->find($id);

        if (!$option) {
            return response()->json(['message' => 'Option de réponse introuvable.'], 404);
        }

        return response()->json($option, 200);
    }

    /**
     * Ajoute un nouveau choix de réponse à une question (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Doit contenir `texte_option`.
     * @param  int     $id_question Identifiant de la question cible.
     * @return JsonResponse Choix créé (201) ou 404 si la question est introuvable.
     */
    public function store(Request $request, $id_question): JsonResponse
    {
        $question = Question::find($id_question);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        $validatedData = $request->validate([
            'texte_option' => 'required|string|max:255',
        ]);

        $option = OptionQuestion::create([
            'id_question'  => $id_question,
            'texte_option' => $validatedData['texte_option'],
        ]);

        return response()->json([
            'message' => 'Choix de réponse ajouté avec succès.',
            'option'  => $option,
        ], 201);
    }

    /**
     * Met à jour le texte d'un choix de réponse existant (vue admin).
     * @author Neris Alessandro
     * @param  Request $request Doit contenir `texte_option`.
     * @param  int     $id      Identifiant du choix à modifier.
     * @return JsonResponse Choix mis à jour (200) ou 404.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $option = OptionQuestion::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option de réponse introuvable.'], 404);
        }

        $validatedData = $request->validate([
            'texte_option' => 'required|string|max:255',
        ]);

        $option->update($validatedData);

        return response()->json([
            'message' => 'Choix de réponse mis à jour avec succès.',
            'option'  => $option,
        ], 200);
    }

    /**
     * Supprime définitivement un choix de réponse (vue admin).
     * @author Neris Alessandro
     * @param  int $id Identifiant du choix à supprimer.
     * @return JsonResponse Message de confirmation (200) ou 404.
     */
    public function destroy($id): JsonResponse
    {
        $option = OptionQuestion::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option de réponse introuvable.'], 404);
        }

        $option->delete();

        return response()->json(['message' => 'Choix de réponse supprimé avec succès.'], 200);
    }
}