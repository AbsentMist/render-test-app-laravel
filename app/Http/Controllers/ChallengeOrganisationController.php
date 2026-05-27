<?php

/**
 * @fileoverview ChallengeOrganisationController.php
 * @description Contrôleur gérant les organisations participantes aux challenges d'une course
 *              (groupes d'entreprise ou associations). Permet à l'organisateur de définir
 *              la liste des entités qui peuvent s'inscrire au challenge, avec protection
 *              contre les doublons.
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\ChallengeOrganisation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChallengeOrganisationController extends Controller
{
    /**
     * Retourne toutes les organisations d'une course, triées par type puis par nom.
     * Accessible à l'organisateur et au participant (pour le formulaire d'inscription).
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Liste des organisations triées.
     */
    public function index($id_course): JsonResponse
    {
        $organisations = ChallengeOrganisation::where('id_course', $id_course)
            ->orderBy('type')
            ->orderBy('nom')
            ->get();

        return response()->json($organisations, 200);
    }

    /**
     * Crée une nouvelle organisation pour le challenge d'une course.
     * Protège contre les doublons : retourne 409 si une organisation avec le même
     * nom, type et course existe déjà.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Données de l'organisation (id_course, nom, type).
     * @return JsonResponse Organisation créée (201) ou 409 si doublon.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_course' => 'required|integer|exists:Course,id',
            'nom'       => 'required|string|max:100',
            'type'      => 'required|in:Groupe,Entreprise',
        ]);

        // Vérifie l'unicité sur la combinaison (course, nom, type) avant création
        $existe = ChallengeOrganisation::where('id_course', $validated['id_course'])
            ->where('nom', $validated['nom'])
            ->where('type', $validated['type'])
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Cette organisation existe déjà pour cette course.'
            ], 409);
        }

        $organisation = ChallengeOrganisation::create($validated);

        return response()->json($organisation, 201);
    }

    /**
     * Supprime une organisation du challenge (vue organisateur uniquement).
     * @author Guillermet Jean-Daniel
     * @param  int $id Identifiant de l'organisation à supprimer.
     * @return JsonResponse Message de confirmation (200) ou 404 si introuvable.
     */
    public function destroy($id): JsonResponse
    {
        $organisation = ChallengeOrganisation::find($id);

        if (!$organisation) {
            return response()->json(['message' => 'Organisation introuvable.'], 404);
        }

        $organisation->delete();

        return response()->json(['message' => 'Organisation supprimée.'], 200);
    }
}