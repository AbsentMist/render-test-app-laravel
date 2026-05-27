<?php

/**
 * @fileoverview QuestionController.php
 * @description Contrôleur gérant les questions du questionnaire d'inscription.
 *              Chaque question peut être liée à plusieurs courses via la table pivot
 *              CourseQuestion (qui stocke aussi l'ordre d'affichage).
 *              Le flag `modele` distingue les questions réutilisables des questions
 *              créées spécifiquement pour une course.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Retourne toutes les questions marquées comme modèles réutilisables, avec leurs
     * cours associées et leurs choix de réponses (vue admin).
     * @author Neris Alessandro
     * @return JsonResponse Liste des questions modèles avec leurs relations.
     */
    public function indexAdmin(): JsonResponse
    {
        $questions = Question::where('modele', true)
            ->with(['courses', 'choix'])
            ->get();

        return response()->json($questions, 200);
    }

    /**
     * Retourne les questions d'une course spécifique, triées par l'ordre défini
     * dans la table pivot CourseQuestion (vue participant).
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Questions triées par ordre avec leurs choix, ou 404 si aucune.
     */
    public function indexParticipant($id_course): JsonResponse
    {
        $questions = Question::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })
        ->with(['choix'])
        ->get()
        // Tri par le champ `ordre` de la table pivot pour respecter l'ordre défini par l'admin
        ->sortBy(fn($q) => $q->courses->firstWhere('id', $id_course)?->pivot->ordre ?? 0)
        ->values();

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'Aucune question disponible pour cette course.'], 404);
        }

        return response()->json($questions, 200);
    }

    /**
     * Retourne le détail d'une question avec ses cours, choix et réponses.
     * @author Neris Alessandro
     * @param  int $id Identifiant de la question.
     * @return JsonResponse Question avec ses relations ou 404.
     */
    public function show($id): JsonResponse
    {
        $question = Question::with(['courses', 'choix', 'reponses'])->find($id);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        return response()->json($question, 200);
    }

    /**
     * Crée une nouvelle question et l'associe aux courses fournies.
     * L'ordre est calculé automatiquement pour chaque course : max(ordre) + 1.
     * La création est atomique : question + liaisons sont créées ensemble ou pas du tout.
     * @author Neris Alessandro
     * @param  Request $request Données de la question (enonce, modele, ids_courses[]).
     * @return JsonResponse Question créée avec ses relations (201) ou erreur (500).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'enonce'        => 'required|string|max:255',
            'modele'        => 'boolean',
            // Le frontend Vue.js envoie `ids_courses` (tableau d'IDs de courses)
            'ids_courses'   => 'nullable|array',
            'ids_courses.*' => 'exists:Course,id',
        ]);

        DB::beginTransaction();
        try {
            $question = Question::create([
                'enonce' => $validatedData['enonce'],
                'modele' => $validatedData['modele'] ?? false,
            ]);

            // Attache la question à chaque course avec un ordre calculé automatiquement
            if (!empty($validatedData['ids_courses'])) {
                foreach ($validatedData['ids_courses'] as $id_course) {
                    $dernierOrdre = \DB::table('CourseQuestion')
                        ->where('id_course', $id_course)
                        ->max('ordre') ?? 0;

                    $question->courses()->attach($id_course, ['ordre' => $dernierOrdre + 1]);
                }
            }

            DB::commit();
            return response()->json([
                'message'  => 'Question créée avec succès.',
                'question' => Question::with(['courses', 'choix'])->find($question->id),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Met à jour une question existante et synchronise ses liaisons courses.
     * Pour les cours déjà liées, l'ordre existant est préservé.
     * Pour les nouvelles cours, l'ordre est calculé automatiquement.
     * @author Neris Alessandro
     * @param  Request $request Champs à mettre à jour (enonce, modele, courses[]).
     * @param  int     $id      Identifiant de la question.
     * @return JsonResponse Question mise à jour avec ses relations (200) ou erreur.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        $validatedData = $request->validate([
            'enonce'    => 'sometimes|required|string|max:255',
            'modele'    => 'boolean',
            'courses'   => 'sometimes|array',
            'courses.*' => 'exists:Course,id',
        ]);

        DB::beginTransaction();
        try {
            // Met à jour uniquement les champs de base
            $question->update(array_intersect_key($validatedData, array_flip(['enonce', 'modele'])));

            if ($request->has('courses')) {
                // Construit le tableau de sync en préservant l'ordre des liaisons existantes
                $sync = [];
                foreach ($validatedData['courses'] as $id_course) {
                    $existant = \App\Models\CourseQuestion::where('id_course', $id_course)
                        ->where('id_question', $question->id)
                        ->first();

                    // Preserve l'ordre existant ou calcule le prochain ordre disponible
                    $ordre = $existant?->ordre
                        ?? (\App\Models\CourseQuestion::where('id_course', $id_course)->max('ordre') ?? 0) + 1;

                    $sync[$id_course] = ['ordre' => $ordre];
                }
                $question->courses()->sync($sync);
            }

            DB::commit();

            return response()->json([
                'message'  => 'Question mise à jour avec succès.',
                'question' => Question::with(['courses', 'choix'])->find($question->id),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Supprime définitivement une question (vue admin).
     * @author Neris Alessandro
     * @param  int $id Identifiant de la question à supprimer.
     * @return JsonResponse Message de confirmation (200) ou 404.
     */
    public function destroy($id): JsonResponse
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Question supprimée avec succès.'], 200);
    }
}