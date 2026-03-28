<?php

namespace App\Http\Controllers;

use App\Models\OptionQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OptionQuestionController extends Controller
{
    // GET - Tous les choix d'une question
    public function index($id_question): JsonResponse
    {
        $question = Question::find($id_question);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        return response()->json($question->choix, 200);
    }

    // GET
    public function show($id): JsonResponse
    {
        $option = OptionQuestion::with(['question'])->find($id);

        if (!$option) {
            return response()->json(['message' => 'Option de réponse introuvable.'], 404);
        }

        return response()->json($option, 200);
    }

    // POST (Admin) - Ajouter un choix à une question
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

    // PUT (Admin)
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

    // DELETE (Admin)
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