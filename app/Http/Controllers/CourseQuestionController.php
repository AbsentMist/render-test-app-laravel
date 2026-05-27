<?php

/**
 * @fileoverview CourseQuestionController.php
 * @description Contrôleur gérant la relation entre les courses et leurs questions de questionnaire.
 *              Permet de consulter les questions d'une course dans leur ordre d'affichage
 *              et de réordonner les questions par glisser-déposer dans l'interface admin.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\CourseQuestion;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CourseQuestionController extends Controller
{
    /**
     * Retourne les questions d'une course avec leur ordre d'affichage et leurs choix de réponses.
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Questions triées par ordre ou 404 si la course est introuvable.
     */
    public function index($id_course): JsonResponse
    {
        $course = Course::find($id_course);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        $questions = CourseQuestion::with(['question.choix'])
            ->where('id_course', $id_course)
            ->orderBy('ordre')
            ->get();

        return response()->json($questions, 200);
    }

    /**
     * Met à jour l'ordre d'affichage des questions d'une course.
     * Attend un tableau d'objets `{id_question, ordre}` correspondant au nouvel ordre.
     * La mise à jour est atomique : toutes les questions sont réordonnées ou aucune.
     * @author Neris Alessandro
     * @param  Request $request Tableau `questions` : [{ id_question: int, ordre: int }, ...].
     * @param  int     $id_course Identifiant de la course.
     * @return JsonResponse Questions réordonnées (200) ou erreur (500).
     */
    public function reordonner(Request $request, $id_course): JsonResponse
    {
        $request->validate([
            'questions'               => 'required|array|min:1',
            'questions.*.id_question' => 'required|exists:Question,id',
            'questions.*.ordre'       => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->input('questions') as $item) {
                CourseQuestion::where('id_course', $id_course)
                    ->where('id_question', $item['id_question'])
                    ->update(['ordre' => $item['ordre']]);
            }

            DB::commit();

            return response()->json([
                'message'   => 'Ordre des questions mis à jour avec succès.',
                'questions' => CourseQuestion::where('id_course', $id_course)
                                ->orderBy('ordre')
                                ->get(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}