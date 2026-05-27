<?php

/**
 * @fileoverview ReponseQuestionController.php
 * @description Contrôleur gérant les réponses des participants aux questions du questionnaire.
 *              Chaque inscription peut avoir une réponse par question.
 *              L'enregistrement est idempotent (updateOrCreate) pour permettre
 *              la modification des réponses avant validation finale.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\ReponseQuestion;
use App\Models\Question;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReponseQuestionController extends Controller
{
    /**
     * Retourne toutes les réponses associées à une inscription spécifique.
     * Utilisé pour pré-remplir le questionnaire lors d'une modification d'inscription.
     * @author Neris Alessandro
     * @param  int $id_inscription Identifiant de l'inscription.
     * @return JsonResponse Réponses avec les questions et options choisies, ou 404.
     */
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

    /**
     * Retourne toutes les réponses à une question donnée (vue admin — statistiques).
     * Permet à l'organisateur de voir la répartition des réponses pour une question.
     * @author Neris Alessandro
     * @param  int $id_question Identifiant de la question.
     * @return JsonResponse Réponses avec les inscriptions et options, ou 404.
     */
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

    /**
     * Enregistre ou met à jour les réponses d'un participant pour son inscription.
     * Accepte un tableau de réponses pour traiter toutes les questions en une seule requête.
     * Utilise updateOrCreate pour garantir l'unicité inscription × question et permettre
     * la correction des réponses. L'opération est atomique.
     * @author Neris Alessandro
     * @param  Request $request Tableau `reponses` : [{ id_inscription, id_question, id_option_choisie? }].
     * @return JsonResponse Réponses enregistrées (201) ou erreur (500).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'reponses'                     => 'required|array|min:1',
            'reponses.*.id_inscription'    => 'required|exists:Inscription,id',
            'reponses.*.id_question'       => 'required|exists:Question,id',
            // Nullable : une question sans réponse sélectionnée est autorisée
            'reponses.*.id_option_choisie' => 'nullable|exists:OptionQuestion,id',
        ]);

        DB::beginTransaction();
        try {
            $creees = [];

            foreach ($request->input('reponses') as $data) {
                // Garantit l'unicité inscription × question ; met à jour si déjà existante
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

    /**
     * Supprime la réponse d'un participant à une question spécifique.
     * Identifié par la combinaison inscription × question.
     * @author Neris Alessandro
     * @param  int $id_inscription Identifiant de l'inscription.
     * @param  int $id_question    Identifiant de la question.
     * @return JsonResponse Message de confirmation (200) ou 404.
     */
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