<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\OptionQuantifiable;
use App\Models\OptionCochable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Controller qui gère les options : Option, OptionQuantifiable, OptionCochable et OptionPourCourse
 */
class OptionController extends Controller
{
    // GET (Admin)
    public function indexAdmin(): JsonResponse
    {
        // Récupération des options avec leurs relations
        $options = Option::where('modele', true)->with(['courses', 'quantifiable', 'cochable'])->get();
        return response()->json($options, 200);
    }

    /**
     * Récupérer toutes les options d'une course spécifique
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

    // GET (Admin)
    public function show($id): JsonResponse
    {
        $option = Option::with(['courses', 'quantifiable', 'cochable'])->find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        return response()->json($option, 200);
    }

    // POST (Admin) 
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nom'         => 'required|string|max:80',
            'tarif'       => 'required|numeric|min:0',
            'type'        => 'required|string|in:Quantifiable,Cochable', 
            'description' => 'required|string|max:255',
            'modele'      => 'boolean', // Nouveau champ booléen
            
            // Validation OptionQuantifiable
            'quantiteMin' => 'required_if:type,Quantifiable|integer|min:0',
            'quantiteMax' => 'required_if:type,Quantifiable|integer|gte:quantiteMin',
            
            // Validation des liaisons courses
            'courses'     => 'array',
            'courses.*'   => 'exists:Course,id'
        ]);

        DB::beginTransaction();
        try {
            // Création de l'Option (sans le champ image)
            $option = Option::create([
                'nom'         => $validatedData['nom'],
                'tarif'       => $validatedData['tarif'],
                'type'        => $validatedData['type'],
                'description' => $validatedData['description'],
                'modele'      => $validatedData['modele'] ?? false, // Par défaut à false
            ]);

            // Création des détails techniques selon le type
            if ($validatedData['type'] === 'Quantifiable') {
                OptionQuantifiable::create([
                    'id'          => $option->id,
                    'quantiteMin' => $validatedData['quantiteMin'],
                    'quantiteMax' => $validatedData['quantiteMax']
                ]);
            } else {
                OptionCochable::create([
                    'id'       => $option->id,
                    'is_coche' => 0 
                ]);
            }

            // Liaison avec les courses si présentes
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

    // PUT (Admin)
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
            'courses.*'   => 'exists:Course,id'
        ];

        if ($option->type === 'Quantifiable') {
            $rules['quantiteMin'] = 'sometimes|required|integer|min:0';
            $rules['quantiteMax'] = 'sometimes|required|integer|gte:quantiteMin';
        }

        $validatedData = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Mise à jour des champs de base (image retirée)
            $option->update(array_intersect_key($validatedData, array_flip(['nom', 'tarif', 'description', 'modele'])));

            // Mise à jour de la table OptionQuantifiable si nécessaire
            if ($option->type === 'Quantifiable') {
                $quantifiableData = [];
                if (isset($validatedData['quantiteMin'])) $quantifiableData['quantiteMin'] = $validatedData['quantiteMin'];
                if (isset($validatedData['quantiteMax'])) $quantifiableData['quantiteMax'] = $validatedData['quantiteMax'];
                
                if (!empty($quantifiableData)) {
                    OptionQuantifiable::where('id', $option->id)->update($quantifiableData);
                }
            }

            // Synchronisation des courses
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

    // DELETE (Admin)
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