<?php

/**
 * @fileoverview ChoixOptionController.php
 * @description Contrôleur gérant les choix d'options effectués par les participants lors
 *              de l'inscription (ex: 2 repas, transport inclus).
 *              Les opérations de création/mise à jour utilisent firstOrNew + save
 *              plutôt que updateOrCreate car la clé primaire composite de ChoixOption
 *              n'est pas compatible avec updateOrCreate.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Models\ChoixOption;
use App\Models\Inscription;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChoixOptionController extends Controller
{
    /**
     * Retourne tous les choix d'options d'une inscription avec les détails de chaque option.
     * @author Neris Alessandro
     * @param  int $id_inscription Identifiant de l'inscription.
     * @return JsonResponse Liste des choix avec leurs options (quantifiable/cochable) ou 404.
     */
    public function indexParInscription($id_inscription): JsonResponse
    {
        $inscription = Inscription::find($id_inscription);

        if (!$inscription) {
            return response()->json(['message' => 'Inscription introuvable.'], 404);
        }

        $choix = ChoixOption::with(['option.quantifiable', 'option.cochable'])
            ->where('id_inscription', $id_inscription)
            ->get();

        return response()->json($choix, 200);
    }

    /**
     * Retourne tous les choix effectués pour une option spécifique (vue admin — statistiques).
     * Permet de savoir combien d'inscriptions ont sélectionné une option donnée.
     * @author Neris Alessandro
     * @param  int $id_option Identifiant de l'option.
     * @return JsonResponse Liste des choix avec les inscriptions associées ou 404.
     */
    public function indexParOption($id_option): JsonResponse
    {
        $option = Option::find($id_option);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        $choix = ChoixOption::with(['inscription'])
            ->where('id_option', $id_option)
            ->get();

        return response()->json($choix, 200);
    }

    /**
     * Enregistre ou met à jour les choix d'options d'une inscription.
     * Accepte un tableau pour traiter toutes les options en une seule requête.
     * Vérifie que chaque option appartient bien à la course de l'inscription.
     * Utilise firstOrNew + save (et non updateOrCreate) car la clé primaire composite
     * n'est pas supportée par updateOrCreate.
     * @author Neris Alessandro
     * @param  Request $request Tableau `choix` : [{ id_inscription, id_option, quantite? }].
     * @return JsonResponse Choix enregistrés (201) ou erreur (422/500).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'choix'                  => 'required|array|min:1',
            'choix.*.id_inscription' => 'required|exists:Inscription,id',
            'choix.*.id_option'      => 'required|exists:Options,id',
            'choix.*.quantite'       => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $crees = [];

            foreach ($request->input('choix') as $data) {
                $inscription  = Inscription::find($data['id_inscription']);

                // Vérifie que l'option est bien disponible pour la course de cette inscription
                $optionValide = Option::whereHas('courses', function ($query) use ($inscription) {
                    $query->where('id_course', $inscription->id_course);
                })->find($data['id_option']);

                if (!$optionValide) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "L'option {$data['id_option']} n'appartient pas à la course de cette inscription.",
                    ], 422);
                }

                // Mise à jour si le choix existe, création sinon (clé composite incompatible avec updateOrCreate)
                $choix = ChoixOption::where('id_inscription', $data['id_inscription'])
                    ->where('id_option', $data['id_option'])
                    ->first();

                if ($choix) {
                    ChoixOption::where('id_inscription', $data['id_inscription'])
                        ->where('id_option', $data['id_option'])
                        ->update(['quantite' => $data['quantite'] ?? null]);

                    $choix = ChoixOption::where('id_inscription', $data['id_inscription'])
                        ->where('id_option', $data['id_option'])
                        ->first();
                } else {
                    $choix = new ChoixOption();
                    $choix->id_inscription = $data['id_inscription'];
                    $choix->id_option      = $data['id_option'];
                    $choix->quantite       = $data['quantite'] ?? null;
                    $choix->save();
                }

                $crees[] = $choix;
            }

            DB::commit();

            return response()->json([
                'message' => 'Choix d\'options enregistrés avec succès.',
                'choix'   => $crees,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Met à jour la quantité d'un choix d'option existant.
     * Identifié par la clé composite (id_inscription, id_option).
     * @author Neris Alessandro
     * @param  Request $request        Doit contenir `quantite`.
     * @param  int     $id_inscription Identifiant de l'inscription.
     * @param  int     $id_option      Identifiant de l'option.
     * @return JsonResponse Choix mis à jour (200) ou 404.
     */
    public function update(Request $request, $id_inscription, $id_option): JsonResponse
    {
        $choix = ChoixOption::where('id_inscription', $id_inscription)
            ->where('id_option', $id_option)
            ->first();

        if (!$choix) {
            return response()->json(['message' => 'Choix introuvable.'], 404);
        }

        $request->validate([
            'quantite' => 'required|integer|min:0',
        ]);

        // Mise à jour via requête directe (évite les problèmes de clé composite avec save())
        ChoixOption::where('id_inscription', $id_inscription)
            ->where('id_option', $id_option)
            ->update(['quantite' => $request->input('quantite')]);

        $choix = ChoixOption::where('id_inscription', $id_inscription)
            ->where('id_option', $id_option)
            ->first();

        return response()->json([
            'message' => 'Choix mis à jour avec succès.',
            'choix'   => $choix,
        ], 200);
    }

    /**
     * Supprime un choix d'option spécifique identifié par sa clé composite.
     * @author Neris Alessandro
     * @param  int $id_inscription Identifiant de l'inscription.
     * @param  int $id_option      Identifiant de l'option.
     * @return JsonResponse Confirmation (200) ou 404.
     */
    public function destroy($id_inscription, $id_option): JsonResponse
    {
        $choix = ChoixOption::where('id_inscription', $id_inscription)
            ->where('id_option', $id_option)
            ->first();

        if (!$choix) {
            return response()->json(['message' => 'Choix introuvable.'], 404);
        }

        ChoixOption::where('id_inscription', $id_inscription)
            ->where('id_option', $id_option)
            ->delete();

        return response()->json(['message' => 'Choix supprimé avec succès.'], 200);
    }
}