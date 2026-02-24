<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\OptionQuantifiable;
use App\Models\OptionCochable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
/**
 * Controller qui gère les options des tables : Option, OptionQuantifiable, OptionCochable et OptionPourCourse
 */
class OptionController extends Controller
{
    // GET (Admin)
    public function indexAdmin(): JsonResponse
    {
        // On récupère les options avec les courses et les tables (quantifiable/cochable)
        $options = Option::with(['courses', 'quantifiable', 'cochable'])->get()->map(function ($option) {
            //pour la gestion de l'image, conversion en base64 pour le frontend
            if ($option->image) {
                $option->image = 'data:image/jpeg;base64,' . base64_encode($option->image);
            }
            return $option;
        });

        return response()->json($options, 200);
    }

    /**
     * Cette méthode permet de récupérer toutes les options d'une course spécifique pour les participants
     */
    public function indexParticipant($id_course): JsonResponse
    {
        
        $options = Option::whereHas('courses', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })
        ->with(['quantifiable', 'cochable']) 
        ->get()
        ->map(function ($option) {
            // Conversion de l'image en Base64 pour le frontend
            if ($option->image) {
                $option->image = 'data:image/jpeg;base64,' . base64_encode($option->image);
            }
            return $option;
        });

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

        // Conversion de l'image en Base64 pour le frontend
        if ($option->image) {
            $option->image = 'data:image/jpeg;base64,' . base64_encode($option->image);
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
            'image'       => 'nullable|file|image|max:2048',
            
            // OptionQuantifiable
            'quantiteMin' => 'required_if:type,Quantifiable|integer|min:0',
            'quantiteMax' => 'required_if:type,Quantifiable|integer|gte:quantiteMin',
            
            // Vérifier si les courses existent
            'courses'     => 'required|array',
            'courses.*'   => 'exists:Course,id'
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = file_get_contents($request->file('image')->getRealPath());
        }

        DB::beginTransaction();
        try {
            // Création de l'Option
            $option = Option::create([
                'nom'         => $validatedData['nom'],
                'tarif'       => $validatedData['tarif'],
                'type'        => $validatedData['type'],
                'description' => $validatedData['description'],
                'image'       => $validatedData['image'] ?? null,
            ]);

            //Création OptionQuantifiable ou OptionCochable
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

            // Liaison avec les courses (mise à jour de la table OptionPourCourse)
            $option->courses()->attach($request->input('courses'));

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

        // Règles de validation de base
        $rules = [
            'nom'         => 'sometimes|required|string|max:100',
            'tarif'       => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|required|string|max:255',
            'image'       => 'nullable|file|image|max:2048',
            'courses'     => 'sometimes|array',
            'courses.*'   => 'exists:Course,id'
        ];

        // Si l'option est Quantifiable
        if ($option->type === 'Quantifiable') {
            $rules['quantiteMin'] = 'sometimes|required|integer|min:0';
            $rules['quantiteMax'] = 'sometimes|required|integer|gte:quantiteMin';
        }

        $validatedData = $request->validate($rules);

        DB::beginTransaction();
        try {
            //Conversion en BLOB de l'image pour la DB
            if ($request->hasFile('image')) {
                $validatedData['image'] = file_get_contents($request->file('image')->getRealPath());
            }

            // Mise à jour de l'option
            $option->update(array_intersect_key($validatedData, array_flip(['nom', 'tarif', 'description', 'image'])));

            // Mise à jour de OptionQuantifiable
            if ($option->type === 'Quantifiable') {
                $quantifiableData = [];
                if (isset($validatedData['quantiteMin'])) $quantifiableData['quantiteMin'] = $validatedData['quantiteMin'];
                if (isset($validatedData['quantiteMax'])) $quantifiableData['quantiteMax'] = $validatedData['quantiteMax'];
                
                if (!empty($quantifiableData)) {
                    OptionQuantifiable::where('id', $option->id)->update($quantifiableData);
                }
            }

            //Synchronisation des courses
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

    // DELETE : Supprimer une option (Admin)
    public function destroy($id): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        // Supression de l'option parent qui va automatiquement supprimer les enfants associés
        $option->delete(); 

        return response()->json(['message' => 'Option supprimée avec succès.'], 200);
    }
}