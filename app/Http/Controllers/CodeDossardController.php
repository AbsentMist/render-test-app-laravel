<?php

/**
 * @fileoverview CodeDossardController.php
 * @description Contrôleur gérant les codes dossard personnalisés associés aux courses :
 *              création, modification, suppression (vue organisateur) et
 *              validation côté participant lors de la saisie dans le panier.
 *              Un code dossard permet d'attribuer un nom personnalisé à un dossard
 *              plutôt qu'un numéro généré automatiquement.
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\CodeDossard;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeDossardController extends Controller
{
    /**
     * Retourne tous les codes dossard d'une course, triés du plus récent au plus ancien.
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course.
     * @return \Illuminate\Http\JsonResponse Liste des codes dossard.
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
     * Crée un nouveau code dossard pour une course.
     * Le compteur d'utilisations est initialisé à 0 à la création.
     * @author Guillermet Jean-Daniel
     * @param  Request $request  Données du code (code, nom_personnalise, utilisations_max).
     * @param  int     $id_course Identifiant de la course cible.
     * @return \Illuminate\Http\JsonResponse Code créé (201).
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
     * Met à jour un code dossard existant.
     * La limite d'utilisations ne peut pas être abaissée en dessous du nombre
     * d'utilisations déjà effectuées pour éviter les incohérences.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant du code dossard.
     * @return \Illuminate\Http\JsonResponse Code mis à jour.
     */
    public function update(Request $request, $id)
    {
        $code = CodeDossard::findOrFail($id);

        $validated = $request->validate([
            // Ignore l'unicité pour l'enregistrement courant afin d'éviter un faux conflit
            'code'             => 'sometimes|string|max:50|unique:CodeDossard,code,' . $id,
            'nom_personnalise' => 'nullable|string|max:150',
            // La limite ne peut pas descendre sous le nombre d'utilisations déjà effectuées
            'utilisations_max' => 'sometimes|integer|min:' . $code->utilisations_actuelles,
        ]);

        $code->update($validated);

        return response()->json($code);
    }

    /**
     * Supprime définitivement un code dossard.
     * @author Guillermet Jean-Daniel
     * @param  int $id Identifiant du code à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function destroy($id)
    {
        $code = CodeDossard::findOrFail($id);
        $code->delete();

        return response()->json(['message' => 'Code supprimé avec succès.']);
    }

    /**
     * Valide un code dossard saisi par un participant dans le panier.
     * Vérifie l'existence et la validité (limite d'utilisation non atteinte).
     * Retourne le nom personnalisé si défini, sinon indique qu'un numéro sera attribué automatiquement.
     * La comparaison du code est insensible à la casse (conversion en majuscules).
     * @author Guillermet Jean-Daniel
     * @param  Request $request Doit contenir `code` et `id_course`.
     * @return \Illuminate\Http\JsonResponse `{valide, code, nom_personnalise, message}`.
     */
    public function valider(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string',
            'id_course' => 'required|exists:Course,id',
        ]);

        // Recherche insensible à la casse : le code est toujours stocké en majuscules
        $code = CodeDossard::where('code', strtoupper($validated['code']))
            ->where('id_course', $validated['id_course'])
            ->first();

        if (!$code) {
            return response()->json([
                'valide'  => false,
                'message' => 'Code dossard invalide ou non applicable à cette course.',
            ], 404);
        }

        // Vérifie via le modèle que le code n'a pas atteint sa limite d'utilisation
        if (!$code->estValide()) {
            return response()->json([
                'valide'  => false,
                'message' => 'Ce code a atteint sa limite d\'utilisation.',
            ], 422);
        }

        return response()->json([
            'valide'          => true,
            'code'            => $code->code,
            'nom_personnalise'=> $code->nom_personnalise,
            // Message adapté selon que le dossard a un nom personnalisé ou sera numéroté
            'message'         => $code->nom_personnalise
                ? "Dossard personnalisé : {$code->nom_personnalise}"
                : "Code valide — dossard numéroté automatiquement",
        ]);
    }
}