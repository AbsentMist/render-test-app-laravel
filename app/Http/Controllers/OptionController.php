<?php

/**
 * @fileoverview OptionController.php
 * @description Contrôleur gérant les options proposées aux participants lors de l'inscription
 *              (ex: repas, t-shirt, transport). Deux sous-types sont supportés :
 *              - Quantifiable : l'utilisateur choisit une quantité (ex: 2 repas)
 *              - Cochable     : case à cocher simple (ex: transport inclus)
 *              Chaque option peut être liée à une ou plusieurs courses via la table pivot
 *              OptionPourCourse. Le flag `modele` indique les options réutilisables.
 * @author Ngoie Steven
 */

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\OptionQuantifiable;
use App\Models\OptionCochable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OptionController extends Controller
{
    /**
     * Retourne toutes les options marquées comme modèles avec leurs relations (vue admin).
     * Charge les relations `courses`, `quantifiable` et `cochable` pour l'affichage complet.
     * @author Neris Alessandro
     * @return JsonResponse Liste des options modèles avec leurs sous-types et courses liées.
     */
    public function indexAdmin(): JsonResponse
    {
        // Charge les relations pour afficher les détails complets de chaque option
        $options = Option::where('modele', true)->with(['courses', 'quantifiable', 'cochable'])->get();
        return response()->json($options, 200);
    }

    /**
     * Retourne toutes les options assignées à une course spécifique (vue participant).
     * @author Ngoie Steven
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Options de la course avec leurs sous-types, ou 404 si aucune.
     */
    public function indexParticipant($id_course): JsonResponse
    {
        $options = Option::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })
        ->with(['quantifiable', 'cochable'])
        ->get();

        if ($options->isEmpty()) {
            return response()->json(['message' => 'Aucune option disponible pour cette course.'], 404);
        }

        return response()->json($options, 200);
    }

    /**
     * Retourne le détail d'une option spécifique avec ses relations (vue admin).
     * @author Ngoie Steven
     * @param  int $id Identifiant de l'option.
     * @return JsonResponse Option avec ses relations ou 404.
     */
    public function show($id): JsonResponse
    {
        $option = Option::with(['courses', 'quantifiable', 'cochable'])->find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        return response()->json($option, 200);
    }

    /**
     * Crée une nouvelle option avec ses détails de sous-type et ses liaisons courses.
     * La création est atomique (transaction) : Option + OptionQuantifiable/OptionCochable
     * + liaisons courses sont créées ensemble ou pas du tout.
     * @author Ngoie Steven
     * @author Neris Alessandro (champ modele, liaison courses)
     * @param  Request $request Données de l'option (nom, tarif, type, description, modele,
     *                          quantiteMin/Max si Quantifiable, courses[]).
     * @return JsonResponse Option créée avec ses relations (201) ou erreur (500).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom'         => 'required|string|max:80',
            'tarif'       => 'required|numeric|min:0',
            'type'        => 'required|string|in:Quantifiable,Cochable',
            'description' => 'required|string|max:255',
            'modele'      => 'boolean',
            // Champs spécifiques aux options quantifiables
            'quantiteMin' => 'required_if:type,Quantifiable|integer|min:0',
            'quantiteMax' => 'required_if:type,Quantifiable|integer|gte:quantiteMin',
            // Liaisons aux courses (optionnel)
            'courses'     => 'array',
            'courses.*'   => 'exists:Course,id',
        ]);

        DB::beginTransaction();
        try {
            // Crée l'option de base (sans les champs de sous-type)
            $option = Option::create([
                'nom'         => $validatedData['nom'],
                'tarif'       => $validatedData['tarif'],
                'type'        => $validatedData['type'],
                'description' => $validatedData['description'],
                'modele'      => $validatedData['modele'] ?? false,
            ]);

            // Crée la table de détails spécifique selon le type de l'option
            if ($validatedData['type'] === 'Quantifiable') {
                OptionQuantifiable::create([
                    'id'          => $option->id,
                    'quantiteMin' => $validatedData['quantiteMin'],
                    'quantiteMax' => $validatedData['quantiteMax'],
                ]);
            } else {
                // Cochable : état initial décoché
                OptionCochable::create([
                    'id'       => $option->id,
                    'is_coche' => 0,
                ]);
            }

            // Lie l'option aux courses si des IDs sont fournis
            if (!empty($request->input('courses'))) {
                $option->courses()->attach($request->input('courses'));
            }

            DB::commit();

            return response()->json([
                'message' => 'Option créée avec succès.',
                'option'  => Option::with(['courses', 'quantifiable', 'cochable'])->find($option->id)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Met à jour une option existante (vue admin).
     * Les règles de validation des champs quantifiables ne sont ajoutées
     * que si l'option est de type Quantifiable.
     * La synchronisation des courses remplace la liste existante par la nouvelle.
     * @author Ngoie Steven
     * @author Neris Alessandro (champ modele, synchronisation courses)
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant de l'option.
     * @return JsonResponse Option mise à jour avec ses relations (200) ou erreur (404/500).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        $rules = [
            'nom'         => 'sometimes|required|string|max:100',
            'tarif'       => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|required|string|max:255',
            'modele'      => 'boolean',
            'courses'     => 'sometimes|array',
            'courses.*'   => 'exists:Course,id',
        ];

        // Ajoute les règles quantifiables uniquement si le type de l'option le requiert
        if ($option->type === 'Quantifiable') {
            $rules['quantiteMin'] = 'sometimes|required|integer|min:0';
            $rules['quantiteMax'] = 'sometimes|required|integer|gte:quantiteMin';
        }

        $validatedData = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Met à jour uniquement les champs de base (le type ne peut pas changer)
            $option->update(array_intersect_key($validatedData, array_flip(['nom', 'tarif', 'description', 'modele'])));

            // Met à jour les bornes de quantité si l'option est quantifiable
            if ($option->type === 'Quantifiable') {
                $quantifiableData = [];
                if (isset($validatedData['quantiteMin'])) $quantifiableData['quantiteMin'] = $validatedData['quantiteMin'];
                if (isset($validatedData['quantiteMax'])) $quantifiableData['quantiteMax'] = $validatedData['quantiteMax'];

                if (!empty($quantifiableData)) {
                    OptionQuantifiable::where('id', $option->id)->update($quantifiableData);
                }
            }

            // Synchronise les courses : remplace la liste existante par la nouvelle
            if ($request->has('courses')) {
                $option->courses()->sync($request->input('courses'));
            }

            DB::commit();

            return response()->json([
                'message' => 'Option mise à jour avec succès.',
                'option'  => Option::with(['courses', 'quantifiable', 'cochable'])->find($option->id)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Supprime définitivement une option (vue admin).
     * @author Ngoie Steven
     * @param  int $id Identifiant de l'option à supprimer.
     * @return JsonResponse Message de confirmation (200) ou 404.
     */
    public function destroy($id): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        $option->delete();

        return response()->json(['message' => 'Option supprimée avec succès.'], 200);
    }
}