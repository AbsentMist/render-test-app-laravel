<?php

/**
 * @fileoverview CodeRabaisController.php
 * @description Contrôleur gérant les codes de rabais associés aux courses :
 *              création, modification, suppression (vue organisateur) et
 *              validation côté participant lors de la saisie dans le panier.
 *              Deux types de rabais sont supportés : pourcentage et montant fixe (CHF).
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\CodeRabais;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeRabaisController extends Controller
{
    /**
     * Retourne tous les codes de rabais d'une course, triés du plus récent au plus ancien.
     * Réservé à l'organisateur/administrateur de la course.
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course.
     * @return \Illuminate\Http\JsonResponse Liste des codes de rabais.
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
     * Crée un nouveau code de rabais pour une course.
     * Valide que la valeur d'un rabais en pourcentage ne dépasse pas 100.
     * Le compteur d'utilisations est initialisé à 0 à la création.
     * @author Guillermet Jean-Daniel
     * @param  Request $request  Données du code (code, type, valeur, utilisations_max, date_expiration, actif).
     * @param  int     $id_course Identifiant de la course cible.
     * @return \Illuminate\Http\JsonResponse Code créé (201).
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

        // Règle métier : un pourcentage de rabais ne peut pas dépasser 100%
        if ($validated['type'] === 'pourcentage' && $validated['valeur'] > 100) {
            return response()->json([
                'message' => 'Un rabais en pourcentage ne peut pas dépasser 100%.'
            ], 422);
        }

        $code = CodeRabais::create([
            ...$validated,
            'id_course'              => $id_course,
            'utilisations_actuelles' => 0,
            'actif'                  => $validated['actif'] ?? true,
        ]);

        return response()->json($code, 201);
    }

    /**
     * Met à jour un code de rabais existant.
     * Tous les champs sont optionnels. Vérifie que le pourcentage reste ≤ 100
     * en tenant compte de la valeur existante si elle n'est pas fournie dans la requête.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant du code de rabais.
     * @return \Illuminate\Http\JsonResponse Code mis à jour.
     */
    public function update(Request $request, $id)
    {
        $code = CodeRabais::with('course')->findOrFail($id);
        $this->verifierOrganisateur($code->course);

        $validated = $request->validate([
            // Ignore l'unicité pour l'enregistrement courant afin d'éviter un faux conflit
            'code'             => 'sometimes|string|max:50|unique:CodeRabais,code,' . $id,
            'type'             => 'sometimes|in:pourcentage,montant_fixe',
            'valeur'           => 'sometimes|numeric|min:0.01',
            'utilisations_max' => 'nullable|integer|min:1',
            'date_expiration'  => 'nullable|date',
            'actif'            => 'sometimes|boolean',
        ]);

        // Si le type est pourcentage, utilise la valeur fournie ou l'ancienne pour la validation
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
     * Supprime définitivement un code de rabais.
     * @author Guillermet Jean-Daniel
     * @param  int $id Identifiant du code à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function destroy($id)
    {
        $code = CodeRabais::with('course')->findOrFail($id);
        $this->verifierOrganisateur($code->course);

        $code->delete();

        return response()->json(['message' => 'Code supprimé avec succès.']);
    }

    /**
     * Valide un code de rabais saisi par un participant dans le panier.
     * Vérifie l'existence, la validité (actif, non expiré, limite non atteinte)
     * et calcule le montant de réduction ainsi que le tarif final.
     * La comparaison du code est insensible à la casse (conversion en majuscules).
     * @author Guillermet Jean-Daniel
     * @param  Request $request Doit contenir `code`, `id_course` et `tarif`.
     * @return \Illuminate\Http\JsonResponse `{valide, code, type, valeur, montant_rabais, tarif_final, message}`.
     */
    public function valider(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string',
            'id_course' => 'required|exists:Course,id',
            'tarif'     => 'required|numeric|min:0',
        ]);

        // Recherche insensible à la casse : le code est toujours stocké en majuscules
        $code = CodeRabais::where('code', strtoupper($validated['code']))
            ->where('id_course', $validated['id_course'])
            ->first();

        if (!$code) {
            return response()->json([
                'valide'  => false,
                'message' => 'Code de rabais invalide ou non applicable à cette course.',
            ], 404);
        }

        // Vérifie via le modèle que le code est actif, non expiré et dans les limites d'utilisation
        if (!$code->estValide()) {
            return response()->json([
                'valide'  => false,
                'message' => 'Ce code de rabais est expiré ou a atteint sa limite d\'utilisation.',
            ], 422);
        }

        $montantRabais = $code->calculerMontantRabais((float) $validated['tarif']);
        // Garantit que le tarif final ne peut pas être négatif
        $tarifFinal    = max((float) $validated['tarif'] - $montantRabais, 0);

        return response()->json([
            'valide'        => true,
            'code'          => $code->code,
            'type'          => $code->type,
            'valeur'        => $code->valeur,
            'montant_rabais'=> $montantRabais,
            'tarif_final'   => $tarifFinal,
            'message'       => $code->type === 'pourcentage'
                ? "-{$code->valeur}% appliqué"
                : "-{$code->valeur} CHF appliqué",
        ]);
    }

    /**
     * Vérifie que l'utilisateur connecté est administrateur de la plateforme.
     * Utilisé en garde sur toutes les opérations de gestion des codes de rabais.
     * @author Guillermet Jean-Daniel
     * @param  Course $course Course dont on vérifie l'accès (non utilisée directement mais conservée pour cohérence de signature).
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si l'utilisateur n'est pas administrateur.
     */
    private function verifierOrganisateur(Course $course): void
    {
        $user    = Auth::user();
        $isAdmin = $user->roles()->where('type', 'Administrateur')->exists();

        if (!$isAdmin) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
    }
}