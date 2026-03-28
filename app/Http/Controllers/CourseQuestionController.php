<?php

namespace App\Http\Controllers;

use App\Models\CourseQuestion;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CourseQuestionController extends Controller
{
    // GET - Questions d'une course avec leur ordre
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

    // PUT - Réordonner les questions d'une course
    // Attend : [{ id_question: 1, ordre: 1 }, { id_question: 3, ordre: 2 }, ...]
    public function reordonner(Request $request, $id_course): JsonResponse
    {
        $request->validate([
            'questions'              => 'required|array|min:1',
            'questions.*.id_question'=> 'required|exists:Question,id',
            'questions.*.ordre'      => 'required|integer|min:1',
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