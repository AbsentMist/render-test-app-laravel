<?php

namespace App\Http\Controllers;

use App\Models\ChoixOption;
use App\Models\Inscription;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChoixOptionController extends Controller
{
    // GET - Tous les choix d'options d'une inscription
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

    // GET - Tous les choix d'une option spécifique (Admin, stats)
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

    // POST (Participant) - Enregistrer les choix d'options d'une inscription
    // Attend un tableau pour traiter toutes les options d'un coup
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
                $inscription = Inscription::find($data['id_inscription']);
                $optionValide = Option::whereHas('courses', function ($query) use ($inscription) {
                    $query->where('id_course', $inscription->id_course);
                })->find($data['id_option']);

                if (!$optionValide) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "L'option {$data['id_option']} n'appartient pas à la course de cette inscription.",
                    ], 422);
                }

                // Remplacement de updateOrCreate par firstOrNew + save
                $choix = ChoixOption::where('id_inscription', $data['id_inscription'])
                    ->where('id_option', $data['id_option'])
                    ->first();

                if ($choix) {
                    $choix->quantite = $data['quantite'] ?? null;
                    $choix->save();
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

    // PUT - Modifier la quantité d'un choix existant
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

        $choix->update(['quantite' => $request->input('quantite')]);

        return response()->json([
            'message' => 'Choix mis à jour avec succès.',
            'choix'   => $choix,
        ], 200);
    }

    // DELETE - Supprimer un choix spécifique
    public function destroy($id_inscription, $id_option): JsonResponse
    {
        $choix = ChoixOption::where('id_inscription', $id_inscription)
            ->where('id_option', $id_option)
            ->first();

        if (!$choix) {
            return response()->json(['message' => 'Choix introuvable.'], 404);
        }

        $choix->delete();

        return response()->json(['message' => 'Choix supprimé avec succès.'], 200);
    }
}