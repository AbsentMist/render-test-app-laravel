<?php

namespace App\Http\Controllers;

use App\Models\CodeDossard;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeDossardController extends Controller
{
    /**
     * GET /organisateur/courses/{id_course}/codes-dossard
     * Liste tous les codes dossard d'une course.
     */
    public function index($id_course)
    {
        Course::findOrFail($id_course);

        $codes = CodeDossard::where('id_course', $id_course)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($codes);
    }

    /**
     * POST /organisateur/courses/{id_course}/codes-dossard
     * Crée un nouveau code dossard pour une course.
     */
    public function store(Request $request, $id_course)
    {
        Course::findOrFail($id_course);

        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:CodeDossard,code',
            'nom_personnalise' => 'nullable|string|max:150',
            'utilisations_max' => 'required|integer|min:1',
        ]);

        $code = CodeDossard::create([
            ...$validated,
            'id_course'              => $id_course,
            'utilisations_actuelles' => 0,
        ]);

        return response()->json($code, 201);
    }

    /**
     * PUT /organisateur/codes-dossard/{id}
     * Modifie un code dossard existant.
     */
    public function update(Request $request, $id)
    {
        $code = CodeDossard::findOrFail($id);

        $validated = $request->validate([
            'code'             => 'sometimes|string|max:50|unique:CodeDossard,code,' . $id,
            'nom_personnalise' => 'nullable|string|max:150',
            'utilisations_max' => 'sometimes|integer|min:' . $code->utilisations_actuelles, // ne peut pas descendre en dessous des utilisations déjà faites
        ]);

        $code->update($validated);

        return response()->json($code);
    }

    /**
     * DELETE /organisateur/codes-dossard/{id}
     * Supprime un code dossard.
     */
    public function destroy($id)
    {
        $code = CodeDossard::findOrFail($id);
        $code->delete();

        return response()->json(['message' => 'Code supprimé avec succès.']);
    }

    /**
     * POST /participant/codes-dossard/valider
     * Valide un code dossard pour une course donnée.
     * Retourne le nom personnalisé si applicable.
     */
    public function valider(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string',
            'id_course' => 'required|exists:Course,id',
        ]);

        $code = CodeDossard::where('code', strtoupper($validated['code']))
            ->where('id_course', $validated['id_course'])
            ->first();

        if (!$code) {
            return response()->json([
                'valide'  => false,
                'message' => 'Code dossard invalide ou non applicable à cette course.',
            ], 404);
        }

        if (!$code->estValide()) {
            return response()->json([
                'valide'  => false,
                'message' => 'Ce code a atteint sa limite d\'utilisation.',
            ], 422);
        }

        return response()->json([
            'valide'          => true,
            'code'            => $code->code,
            'nom_personnalise' => $code->nom_personnalise,
            'message'         => $code->nom_personnalise
                ? "Dossard personnalisé : {$code->nom_personnalise}"
                : "Code valide — dossard numéroté automatiquement",
        ]);
    }
}
