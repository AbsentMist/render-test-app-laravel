<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    //GET (Participant):
    public function indexParticipant($id_evenement): JsonResponse
    {
        $evenement = Evenement::select(
            'id', 'nom', 'date', 'logo', 'couleur_primaire', 'couleur_secondaire'
        )->find($id_evenement);

        if (!$evenement) {
            return response()->json(['message' => 'Événement introuvable.'], 404);
        }

        // Conversion du logo BLOB de la DB en Base64 pour que le frontend puisse l'afficher
        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        // Récupération des courses actives avec leurs catégories
        $courses = Course::with(['categorie', 'sousCategorie'])
            ->withCount('inscriptions')
            ->where('id_evenement', $id_evenement)
            ->where('is_actif', 1) 
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'nom_course' => $course->nom,
                    'tarif' => $course->tarif,
                    'type' => $course->type,
                    'categorie' => $course->categorie ? $course->categorie->nom : null,
                    'sous_categorie' => $course->sousCategorie ? $course->sousCategorie->nom : null,
                    'dossards_restants' => $course->max_inscription 
                        ? ($course->max_inscription - $course->inscriptions_count) 
                        : 'Illimité',
                ];
            });

        return response()->json([
            'evenement' => $evenement,
            'courses' => $courses
        ], 200);
    }

    //GET (Admin):
    public function indexAdmin($id_evenement): JsonResponse
    {
        $evenement = Evenement::find($id_evenement);

        if (!$evenement) {
            return response()->json(['message' => 'Événement introuvable.'], 404);
        }

        //Conversion BLOB en base64 pour frontend
        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        $courses = Course::with(['categorie', 'sousCategorie'])
            ->withCount('inscriptions')
            ->where('id_evenement', $id_evenement)
            ->get();

        return response()->json([
            'evenement' => $evenement,
            'courses' => $courses
        ], 200);
    }

    
    // POST (Admin)
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_evenement'      => 'required|integer|exists:Evenement,id',
            'id_categorie'      => 'nullable|integer|exists:Categorie,id', //à remettre required une fois que les catégories sont créées
            'id_sous_categorie' => 'nullable|integer|exists:SousCategorie,id',
            'nom'               => 'required|string|max:120',
            'date'              => 'required|date',
            'debut_inscription' => 'required|date',
            'fin_inscription'   => 'required|date|after_or_equal:debut_inscription',
            'tarif'             => 'required|numeric|min:0',
            'status'            => 'required|string|max:50',
            'type'              => 'required|string|max:50',
            'max_inscription'   => 'required|integer|min:1',
            'distance'          => 'nullable|numeric|min:0',
            'premier_dossard'   => 'required|integer|min:1',
            'dernier_dossard'   => 'required|integer|gte:premier_dossard',
            'heure_depart'      => 'nullable|date_format:H:i',
            'heure_fin'         => 'nullable|date_format:H:i|after:heure_depart',
            'age_minimum'       => 'required|integer|min:0',
            'age_maximum'       => 'nullable|integer|gte:age_minimum',
            'challenge'         => 'boolean',
            'is_actif'          => 'boolean', 
            'pop_info'          => 'nullable|string|max:255',
        ]);

        // Valeurs par défaut si manquantes
        $validatedData['challenge'] = $validatedData['challenge'] ?? false;
        $validatedData['is_actif'] = $validatedData['is_actif'] ?? true;

        $course = Course::create($validatedData);

        return response()->json([
            'message' => 'Course créée avec succès.',
            'course' => $course
        ], 201);
    }

    
    // GET (Admin / Participant)
    public function show($id): JsonResponse
    {
        $course = Course::with(['categorie', 'sousCategorie', 'evenement'])->find($id);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        // Conversion Logo BLOB en Base64 pour affichage frontend
        if ($course->evenement && $course->evenement->logo) {
            $course->evenement->logo = 'data:image/jpeg;base64,' . base64_encode($course->evenement->logo);
        }

        return response()->json($course, 200);
    }

    
    // PUT (Admin) :
    public function update(Request $request, $id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        $validatedData = $request->validate([
            'id_categorie'      => 'sometimes|required|integer|exists:Categorie,id',
            'id_sous_categorie' => 'nullable|integer|exists:SousCategorie,id',
            'nom'               => 'sometimes|required|string|max:120',
            'date'              => 'sometimes|required|date',
            'debut_inscription' => 'sometimes|required|date',
            'fin_inscription'   => 'sometimes|required|date|after_or_equal:debut_inscription',
            'tarif'             => 'sometimes|required|numeric|min:0',
            'is_actif'          => 'sometimes|required|boolean',
            'max_inscription'   => 'sometimes|required|integer|min:1',
            'premier_dossard'   => 'sometimes|required|integer|min:1',
            'dernier_dossard'   => 'sometimes|required|integer|gte:premier_dossard',
            'distance'          => 'sometimes|required|numeric|min:0',
            'heure_depart'      => 'sometimes|required|date_format:H:i',
            'heure_fin'         => 'sometimes|required|date_format:H:i|after:heure_depart',
            'age_minimum'       => 'sometimes|required|integer|min:0',
            'age_maximum'       => 'sometimes|required|integer|gte:age_minimum',
            'pop_info'          => 'nullable|string|max:255',
        ]);

        $course->update($validatedData);

        return response()->json([
            'message' => 'Course mise à jour avec succès.',
            'course' => $course
        ], 200);
    }

    // DELETE (Admin) :
    public function destroy($id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        $course->delete();

        return response()->json(['message' => 'Course supprimée avec succès.'], 200);
    }
    }