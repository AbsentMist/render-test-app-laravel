<?php

/**
 * @fileoverview PrixEvolutifController.php
 * @description Contrôleur gérant les paliers de prix évolutifs d'une course.
 *              Deux modes de paliers sont supportés :
 *              - "dossards" : le tarif augmente selon le nombre d'inscrits
 *              - "dates"    : le tarif augmente selon la date d'inscription
 *              Accessible en lecture par les participants et en écriture par les organisateurs.
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\PrixEvolutif;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PrixEvolutifController extends Controller
{
    /**
     * Retourne tous les paliers de prix d'une course, triés par ordre croissant.
     * Accessible à l'organisateur et au participant.
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse Liste des paliers triés par ordre.
     */
    public function index($id_course): JsonResponse
    {
        $paliers = PrixEvolutif::where('id_course', $id_course)
            ->orderBy('ordre')
            ->get();

        return response()->json($paliers, 200);
    }

    /**
     * Crée un nouveau palier de prix pour une course.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Données du palier (id_course, type, valeur_debut, valeur_fin, tarif, ordre).
     * @return JsonResponse Palier créé (201).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_course'    => 'required|integer|exists:Course,id',
            'type'         => 'required|in:dossards,dates',
            'valeur_debut' => 'required|string|max:20',
            'valeur_fin'   => 'nullable|string|max:20',
            'tarif'        => 'required|numeric|min:0',
            'ordre'        => 'required|integer|min:1',
        ]);

        $palier = PrixEvolutif::create($validated);

        return response()->json($palier, 201);
    }

    /**
     * Met à jour un palier de prix existant.
     * Tous les champs sont optionnels.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant du palier.
     * @return JsonResponse Palier mis à jour (200).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $palier = PrixEvolutif::findOrFail($id);

        $validated = $request->validate([
            'type'         => 'sometimes|in:dossards,dates',
            'valeur_debut' => 'sometimes|string|max:20',
            'valeur_fin'   => 'nullable|string|max:20',
            'tarif'        => 'sometimes|numeric|min:0',
            'ordre'        => 'sometimes|integer|min:1',
        ]);

        $palier->update($validated);

        return response()->json($palier, 200);
    }

    /**
     * Supprime un palier de prix spécifique.
     * @author Guillermet Jean-Daniel
     * @param  int $id Identifiant du palier à supprimer.
     * @return JsonResponse Message de confirmation (200).
     */
    public function destroy($id): JsonResponse
    {
        $palier = PrixEvolutif::findOrFail($id);
        $palier->delete();

        return response()->json(['message' => 'Palier supprimé.'], 200);
    }

    /**
     * Supprime tous les paliers d'une course d'un seul coup.
     * Utilisé lors du changement de mode de tarification (ex: passage de "dossards" à "dates")
     * pour repartir d'une configuration vierge.
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course dont tous les paliers doivent être supprimés.
     * @return JsonResponse Message de confirmation (200).
     */
    public function destroyByCourse($id_course): JsonResponse
    {
        PrixEvolutif::where('id_course', $id_course)->delete();

        return response()->json(['message' => 'Tous les paliers supprimés.'], 200);
    }

    /**
     * Calcule et retourne le tarif applicable au prochain inscrit pour une course donnée.
     * Utilisé par le frontend pour afficher le bon prix en temps réel au participant.
     *
     * Mode "dossards" : compare le nombre d'inscrits actuels + 1 aux plages de chaque palier.
     * Mode "dates"    : compare la date du jour aux plages de dates de chaque palier.
     *                   Un palier sans valeur_fin s'applique jusqu'à la fin des inscriptions.
     *
     * Si aucun palier ne correspond (hors plage), retourne le tarif de base de la course.
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course.
     * @return JsonResponse `{tarif, palier}` — palier null si tarif de base appliqué.
     */
    public function tarifActuel($id_course): JsonResponse
    {
        $course  = Course::findOrFail($id_course);
        $paliers = PrixEvolutif::where('id_course', $id_course)
            ->orderBy('ordre')
            ->get();

        // Aucun palier configuré : on retourne directement le tarif de base
        if ($paliers->isEmpty()) {
            return response()->json(['tarif' => $course->tarif, 'palier' => null], 200);
        }

        $type         = $paliers->first()->type;
        $tarifTrouve  = $course->tarif; // Fallback sur le tarif de base si aucun palier ne correspond
        $palierTrouve = null;

        if ($type === 'dossards') {
            // +1 pour simuler la position du prochain inscrit
            $nbInscrits = $course->inscriptions()
                ->whereIn('status_paiement', ['Validé', 'En attente'])
                ->count() + 1;

            foreach ($paliers as $palier) {
                $debut = (int) $palier->valeur_debut;
                // Palier sans fin = illimité vers le haut (dernier palier)
                $fin   = $palier->valeur_fin !== null ? (int) $palier->valeur_fin : PHP_INT_MAX;

                if ($nbInscrits >= $debut && $nbInscrits <= $fin) {
                    $tarifTrouve  = $palier->tarif;
                    $palierTrouve = $palier;
                    break;
                }
            }

        } elseif ($type === 'dates') {
            $aujourdhui = Carbon::today();

            foreach ($paliers as $palier) {
                $debut = Carbon::parse($palier->valeur_debut);
                $fin   = $palier->valeur_fin ? Carbon::parse($palier->valeur_fin) : null;

                if ($fin === null) {
                    // Dernier palier sans date de fin : s'applique dès sa date de début
                    if ($aujourdhui->gte($debut)) {
                        $tarifTrouve  = $palier->tarif;
                        $palierTrouve = $palier;
                        break;
                    }
                } else {
                    if ($aujourdhui->between($debut, $fin)) {
                        $tarifTrouve  = $palier->tarif;
                        $palierTrouve = $palier;
                        break;
                    }
                }
            }
        }

        return response()->json([
            'tarif'  => $tarifTrouve,
            'palier' => $palierTrouve,
        ], 200);
    }
}