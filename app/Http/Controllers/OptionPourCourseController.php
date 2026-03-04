<?php
namespace App\Http\Controllers;

use App\Models\OptionPourCourse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OptionPourCourseController extends Controller
{
    /**
     * GET : Liste toutes les options de toutes les courses (Admin)
     */
    public function indexAdmin(): JsonResponse
    {
        $optionsCourse = OptionPourCourse::with(['course', 'option'])->get();
        return response()->json($optionsCourse);
    }

    /**
     * GET : Liste les options d'une course spécifique
     */
    public function indexParticipant($id_course): JsonResponse
    {
        $optionsCourse = OptionPourCourse::with('option')
            ->where('id_course', $id_course)
            ->get();

        if ($optionsCourse->isEmpty()) {
            return response()->json(['message' => 'Aucune option disponible pour cette course.'], 404);
        }

        return response()->json($optionsCourse, 200);
    }

    /**
     * POST : Associer une option à une course (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_course' => 'required|integer|exists:Course,id',
            'id_option' => 'required|integer|exists:Options,id',
        ]);

        // Éviter les doublons sur la clé composite
        $exists = OptionPourCourse::where('id_course', $validatedData['id_course'])
            ->where('id_option', $validatedData['id_option'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Cette option est déjà associée à cette course.'], 409);
        }

        $optionCourse = OptionPourCourse::create($validatedData);

        return response()->json([
            'message'      => 'Option associée à la course avec succès.',
            'optionCourse' => $optionCourse
        ], 201);
    }

    /**
     * GET : Voir une association spécifique via id_course + id_option
     */
    public function show($id_course, $id_option): JsonResponse
    {
        $optionCourse = OptionPourCourse::with(['course', 'option'])
            ->where('id_course', $id_course)
            ->where('id_option', $id_option)
            ->firstOrFail();

        return response()->json($optionCourse);
    }

    /**
     * DELETE : Supprimer une association via id_course + id_option (Admin)
     */
    public function destroy($id_course, $id_option): JsonResponse
    {
        $deleted = OptionPourCourse::where('id_course', $id_course)
            ->where('id_option', $id_option)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Association introuvable.'], 404);
        }

        return response()->json(['message' => 'Association option-course supprimée avec succès.']);
    }

    public function destroyByCourse($id_course): JsonResponse
    {
        $deleted = OptionPourCourse::where('id_course', $id_course)->delete();

        return response()->json([
            'message' => "$deleted association(s) supprimée(s) pour la course $id_course."
        ]);
    }
}