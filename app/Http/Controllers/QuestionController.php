<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    // GET (Admin) - Modèles de questions réutilisables
    public function indexAdmin(): JsonResponse
    {
        $questions = Question::where('modele', true)
            ->with(['courses', 'choix'])
            ->get();

        return response()->json($questions, 200);
    }

    // GET (Participant) - Questions d'une course, triées par ordre du pivot
    public function indexParticipant($id_course): JsonResponse
    {
        $questions = Question::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })
        ->with(['choix'])
        ->get()
        ->sortBy(fn($q) => $q->courses->firstWhere('id', $id_course)?->pivot->ordre ?? 0)
        ->values();

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'Aucune question disponible pour cette course.'], 404);
        }

        return response()->json($questions, 200);
    }

    // GET
    public function show($id): JsonResponse
    {
        $question = Question::with(['courses', 'choix', 'reponses'])->find($id);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        return response()->json($question, 200);
    }

    // POST (Admin)
    // POST (Admin)
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'enonce'      => 'required|string|max:255',
            'modele'      => 'boolean',
            // On accepte 'ids_courses' car c'est ce que ton Vue.js envoie
            'ids_courses' => 'nullable|array', 
            'ids_courses.*' => 'exists:Course,id',
        ]);

        DB::beginTransaction();
        try {
            $question = Question::create([
                'enonce' => $validatedData['enonce'],
                'modele' => $validatedData['modele'] ?? false,
            ]);

            // Utilisation de ids_courses au lieu de courses
            if (!empty($validatedData['ids_courses'])) {
                foreach ($validatedData['ids_courses'] as $id_course) {
                    // Calcul de l'ordre pour cette course spécifique
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

    // PUT (Admin)
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
            $question->update(array_intersect_key($validatedData, array_flip(['enonce', 'modele'])));

            if ($request->has('courses')) {
                // Sync en préservant l'ordre existant, en ajoutant un ordre pour les nouvelles
                $sync = [];
                foreach ($validatedData['courses'] as $id_course) {
                    $existant = \App\Models\CourseQuestion::where('id_course', $id_course)
                        ->where('id_question', $question->id)
                        ->first();

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

    // DELETE (Admin)
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