<?php

namespace App\Http\Controllers;

use App\Models\PrixEvolutif;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PrixEvolutifController extends Controller
{
    // GET - Récupère tous les paliers d'une course (organisateur + participant)
    public function index($id_course): JsonResponse
    {
        $paliers = PrixEvolutif::where('id_course', $id_course)
            ->orderBy('ordre')
            ->get();

        return response()->json($paliers, 200);
    }

    // POST - Crée un palier (organisateur)
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

    // PUT - Modifie un palier (organisateur)
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

    // DELETE - Supprime un palier (organisateur)
    public function destroy($id): JsonResponse
    {
        $palier = PrixEvolutif::findOrFail($id);
        $palier->delete();

        return response()->json(['message' => 'Palier supprimé.'], 200);
    }

    // DELETE - Supprime TOUS les paliers d'une course (lors du changement de mode)
    public function destroyByCourse($id_course): JsonResponse
    {
        PrixEvolutif::where('id_course', $id_course)->delete();

        return response()->json(['message' => 'Tous les paliers supprimés.'], 200);
    }

    // GET - Calcule le tarif actuel pour une course donnée
    // Utilisé par le frontend pour afficher le bon tarif au participant
    public function tarifActuel($id_course): JsonResponse
    {
        $course = Course::findOrFail($id_course);
        $paliers = PrixEvolutif::where('id_course', $id_course)
            ->orderBy('ordre')
            ->get();

        // Si pas de paliers → tarif de base de la course
        if ($paliers->isEmpty()) {
            return response()->json(['tarif' => $course->tarif, 'palier' => null], 200);
        }

        $type = $paliers->first()->type;
        $tarifTrouve = $course->tarif; // fallback
        $palierTrouve = null;

        if ($type === 'dossards') {
            // Compter le nombre d'inscriptions validées pour cette course
            $nbInscrits = $course->inscriptions()
                ->whereIn('status_paiement', ['Validé', 'En attente'])
                ->count();

            foreach ($paliers as $palier) {
                $debut = (int) $palier->valeur_debut;
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
                    // Dernier palier sans date de fin → s'applique jusqu'à la fin
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
