<?php

namespace App\Http\Controllers;

use App\Models\CodeRabais;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeRabaisController extends Controller
{
    /**
     * GET /organisateur/courses/{id_course}/codes-rabais
     * Liste tous les codes de rabais d'une course.
     */
    public function index($id_course)
    {
        $course = Course::findOrFail($id_course);
        $this->verifierOrganisateur($course);

        $codes = CodeRabais::where('id_course', $id_course)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($codes);
    }

    /**
     * POST /organisateur/courses/{id_course}/codes-rabais
     * Crée un nouveau code de rabais pour une course.
     */
    public function store(Request $request, $id_course)
    {
        $course = Course::findOrFail($id_course);
        $this->verifierOrganisateur($course);

        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:CodeRabais,code',
            'type'             => 'required|in:pourcentage,montant_fixe',
            'valeur'           => 'required|numeric|min:0.01',
            'utilisations_max' => 'nullable|integer|min:1',
            'date_expiration'  => 'nullable|date|after:today',
            'actif'            => 'sometimes|boolean',
        ]);

        // Validation métier : un pourcentage ne peut pas dépasser 100
        if ($validated['type'] === 'pourcentage' && $validated['valeur'] > 100) {
            return response()->json([
                'message' => 'Un rabais en pourcentage ne peut pas dépasser 100%.'
            ], 422);
        }

        $code = CodeRabais::create([
            ...$validated,
            'id_course'               => $id_course,
            'utilisations_actuelles'  => 0,
            'actif'                   => $validated['actif'] ?? true,
        ]);

        return response()->json($code, 201);
    }

    /**
     * PUT /organisateur/codes-rabais/{id}
     * Modifie un code de rabais existant.
     */
    public function update(Request $request, $id)
    {
        $code = CodeRabais::with('course')->findOrFail($id);
        $this->verifierOrganisateur($code->course);

        $validated = $request->validate([
            'code'             => 'sometimes|string|max:50|unique:CodeRabais,code,' . $id,
            'type'             => 'sometimes|in:pourcentage,montant_fixe',
            'valeur'           => 'sometimes|numeric|min:0.01',
            'utilisations_max' => 'nullable|integer|min:1',
            'date_expiration'  => 'nullable|date',
            'actif'            => 'sometimes|boolean',
        ]);

        if (isset($validated['type']) && $validated['type'] === 'pourcentage') {
            $valeur = $validated['valeur'] ?? $code->valeur;
            if ($valeur > 100) {
                return response()->json([
                    'message' => 'Un rabais en pourcentage ne peut pas dépasser 100%.'
                ], 422);
            }
        }

        $code->update($validated);

        return response()->json($code);
    }

    /**
     * DELETE /organisateur/codes-rabais/{id}
     * Supprime un code de rabais.
     */
    public function destroy($id)
    {
        $code = CodeRabais::with('course')->findOrFail($id);
        $this->verifierOrganisateur($code->course);

        $code->delete();

        return response()->json(['message' => 'Code supprimé avec succès.']);
    }

    /**
     * POST /participant/codes-rabais/valider
     * Valide un code de rabais pour une course donnée et retourne le montant de réduction.
     * Appelé depuis le frontend lors de la saisie du code dans le panier.
     */
    public function valider(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string',
            'id_course' => 'required|exists:Course,id',
            'tarif'     => 'required|numeric|min:0',
        ]);

        $code = CodeRabais::where('code', strtoupper($validated['code']))
            ->where('id_course', $validated['id_course'])
            ->first();

        if (!$code) {
            return response()->json([
                'valide'  => false,
                'message' => 'Code de rabais invalide ou non applicable à cette course.',
            ], 404);
        }

        if (!$code->estValide()) {
            return response()->json([
                'valide'  => false,
                'message' => 'Ce code de rabais est expiré ou a atteint sa limite d\'utilisation.',
            ], 422);
        }

        $montantRabais = $code->calculerMontantRabais((float) $validated['tarif']);
        $tarifFinal    = max((float) $validated['tarif'] - $montantRabais, 0);

        return response()->json([
            'valide'         => true,
            'code'           => $code->code,
            'type'           => $code->type,
            'valeur'         => $code->valeur,
            'montant_rabais' => $montantRabais,
            'tarif_final'    => $tarifFinal,
            'message'        => $code->type === 'pourcentage'
                ? "-{$code->valeur}% appliqué"
                : "-{$code->valeur} CHF appliqué",
        ]);
    }

    /**
     * Vérifie que l'utilisateur connecté est organisateur de l'événement lié à la course.
     */
    private function verifierOrganisateur(Course $course): void
    {
        $user = Auth::user();
        $isAdmin = $user->roles()->where('type', 'Administrateur')->exists();

        if (!$isAdmin) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
    }
}
