<?php

namespace App\Http\Controllers;

use App\Models\ChallengeOrganisation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChallengeOrganisationController extends Controller
{
    // GET - Récupère toutes les organisations d'une course (organisateur + participant)
    public function index($id_course): JsonResponse
    {
        $organisations = ChallengeOrganisation::where('id_course', $id_course)
            ->orderBy('type')
            ->orderBy('nom')
            ->get();

        return response()->json($organisations, 200);
    }

    // POST - Crée une organisation pour une course (organisateur uniquement)
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_course' => 'required|integer|exists:Course,id',
            'nom'       => 'required|string|max:100',
            'type'      => 'required|in:Groupe,Entreprise',
        ]);

        // Évite les doublons (même nom + même type + même course)
        $existe = ChallengeOrganisation::where('id_course', $validated['id_course'])
            ->where('nom', $validated['nom'])
            ->where('type', $validated['type'])
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Cette organisation existe déjà pour cette course.'], 409);
        }

        $organisation = ChallengeOrganisation::create($validated);

        return response()->json($organisation, 201);
    }

    // DELETE - Supprime une organisation (organisateur uniquement)
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
