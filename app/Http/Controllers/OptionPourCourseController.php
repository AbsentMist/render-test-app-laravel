<?php

/**
 * @fileoverview OptionPourCourseController.php
 * @description Contrôleur gérant les associations entre les options et les courses.
 *              Représente la table pivot OptionPourCourse qui lie une Option à une Course.
 *              Protège contre les doublons sur la clé composite (id_course, id_option).
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\OptionPourCourse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OptionPourCourseController extends Controller
{
    /**
     * Retourne toutes les associations option-course avec leurs relations (vue admin).
     * @author Neris Alessandro
     * @return JsonResponse Liste complète des associations avec leurs courses et options.
     */
    public function indexAdmin(): JsonResponse
    {
        $optionsCourse = OptionPourCourse::with(['course', 'option'])->get();
        return response()->json($optionsCourse);
    }

    /**
     * Retourne les options associées à une course spécifique (vue participant).
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Options de la course ou 404 si aucune.
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
     * Associe une option existante à une course (vue admin).
     * Vérifie l'unicité de la combinaison (id_course, id_option) avant création.
     * @author Neris Alessandro
     * @param  Request $request Doit contenir `id_course` et `id_option`.
     * @return JsonResponse Association créée (201) ou 409 si doublon.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_course' => 'required|integer|exists:Course,id',
            'id_option' => 'required|integer|exists:Options,id',
        ]);

        // Vérifie l'unicité sur la clé composite avant d'insérer
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
     * Retourne une association spécifique identifiée par la clé composite (id_course, id_option).
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @param  int $id_option Identifiant de l'option.
     * @return JsonResponse Association avec ses relations ou 404.
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
     * Supprime une association option-course identifiée par la clé composite.
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course.
     * @param  int $id_option Identifiant de l'option.
     * @return JsonResponse Message de confirmation ou 404 si l'association n'existe pas.
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

    /**
     * Supprime toutes les associations option-course d'une course donnée.
     * Utilisé lors de la suppression d'une course pour nettoyer ses liaisons.
     * @author Neris Alessandro
     * @param  int $id_course Identifiant de la course dont toutes les associations sont supprimées.
     * @return JsonResponse Nombre d'associations supprimées.
     */
    public function destroyByCourse($id_course): JsonResponse
    {
        $deleted = OptionPourCourse::where('id_course', $id_course)->delete();

        return response()->json([
            'message' => "{$deleted} association(s) supprimée(s) pour la course {$id_course}."
        ]);
    }
}