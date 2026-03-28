<?php

namespace App\Http\Controllers;

use App\Models\ReponseQuestion;
use App\Models\Question;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReponseQuestionController extends Controller
{
    // GET - Toutes les réponses d'une inscription
    public function indexParInscription($id_inscription): JsonResponse
    {
        $inscription = Inscription::find($id_inscription);

        if (!$inscription) {
            return response()->json(['message' => 'Inscription introuvable.'], 404);
        }

        $reponses = ReponseQuestion::with(['question', 'option'])
            ->where('id_inscription', $id_inscription)
            ->get();

        return response()->json($reponses, 200);
    }

    // GET - Toutes les réponses d'une question (Admin, stats)
    public function indexParQuestion($id_question): JsonResponse
    {
        $question = Question::find($id_question);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        $reponses = ReponseQuestion::with(['inscription', 'option'])
            ->where('id_question', $id_question)
            ->get();

        return response()->json($reponses, 200);
    }

    // POST (Participant) - Enregistrer les réponses d'une inscription
    // Attend un tableau de réponses pour traiter toutes les questions d'un coup
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'reponses'                    => 'required|array|min:1',
            'reponses.*.id_inscription'   => 'required|exists:Inscription,id',
            'reponses.*.id_question'      => 'required|exists:Question,id',
            'reponses.*.id_option_choisie'=> 'nullable|exists:OptionQuestion,id',
        ]);

        DB::beginTransaction();
        try {
            $creees = [];

            foreach ($request->input('reponses') as $data) {
                // Évite les doublons : une réponse par question par inscription
                $reponse = ReponseQuestion::updateOrCreate(
                    [
                        'id_inscription' => $data['id_inscription'],
                        'id_question'    => $data['id_question'],
                    ],
                    [
                        'id_option_choisie' => $data['id_option_choisie'] ?? null,
                    ]
                );

                $creees[] = $reponse;
            }

            DB::commit();

            return response()->json([
                'message'  => 'Réponses enregistrées avec succès.',
                'reponses' => $creees,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    // DELETE - Supprimer une réponse spécifique
    public function destroy($id_inscription, $id_question): JsonResponse
    {
        $reponse = ReponseQuestion::where('id_inscription', $id_inscription)
            ->where('id_question', $id_question)
            ->first();

        if (!$reponse) {
            return response()->json(['message' => 'Réponse introuvable.'], 404);
        }

        $reponse->delete();

        return response()->json(['message' => 'Réponse supprimée avec succès.'], 200);
    }
}