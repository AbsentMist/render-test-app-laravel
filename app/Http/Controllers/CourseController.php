<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    // GET (Participant)
    public function indexParticipant($id_evenement): JsonResponse
    {
        $evenement = Evenement::select(
            'id', 'nom', 'logo', 'couleur_primaire', 'couleur_secondaire', 'is_document', 'is_questionnaire'
        )->find($id_evenement);

        if (!$evenement) {
            return response()->json(['message' => 'Événement introuvable.'], 404);
        }

        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        // Récupération du questionnaire de l'événement
        $questionnaire = null;
        if ($evenement->is_questionnaire) {
            $questionnaire = \DB::table('EvenementQuestion')
                ->join('Question', 'EvenementQuestion.id_question', '=', 'Question.id')
                ->where('EvenementQuestion.id_evenement', $id_evenement)
                ->orderBy('EvenementQuestion.ordre')
                ->get(['Question.id', 'Question.enonce'])
                ->map(function ($question) {
                    $options = \DB::table('OptionQuestion')
                        ->where('id_question', $question->id)
                        ->get(['id', 'texte_option'])
                        ->toArray();
                    return [
                        'id'       => $question->id,
                        'question' => $question->enonce,
                        'answers'  => array_map(fn($o) => $o->texte_option, $options),
                    ];
                })
                ->toArray();
        }

        $courses = Course::with(['categorie', 'sousCategorie', 'avertissement', 'evenement', 'options.quantifiable', 'options.cochable'])
            ->withCount('inscriptions')
            ->where('id_evenement', $id_evenement)
            ->where('is_actif', true)
            ->get()
            ->map(function ($course) use ($evenement, $questionnaire) {
                return [
                    'id'                => $course->id,
                    'nom_course'        => $course->nom,
                    'tarif'             => $course->tarif,
                    'type'              => $course->type,
                    'is_challenge'      => $course->is_challenge,
                    'categorie'         => $course->categorie ? $course->categorie->nom : null,
                    'sous_categorie'    => $course->sousCategorie ? $course->sousCategorie->nom : null,
                    'avertissement'     => $course->avertissement ?? null,
                    'evenement'         => $course->evenement ?? null,
                    'options'           => $course->options ?? null,
                    'document'          => $evenement->is_document,
                    'questionnaire'     => $questionnaire,
                    'dossards_restants' => $course->max_inscription
                        ? ($course->max_inscription - $course->inscriptions_count)
                        : 'Illimité',
                ];
            });

        return response()->json([
            'evenement' => $evenement,
            'courses'   => $courses
        ], 200);
    }

    // GET (Admin)
    public function indexAdmin($id_evenement): JsonResponse
    {
        $evenement = Evenement::find($id_evenement);

        if (!$evenement) {
            return response()->json(['message' => 'Événement introuvable.'], 404);
        }

        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        $courses = Course::with(['categorie', 'sousCategorie'])
            ->withCount('inscriptions')
            ->where('id_evenement', $id_evenement)
            ->get();

        return response()->json([
            'evenement' => $evenement,
            'courses'   => $courses
        ], 200);
    }

    // GET (Admin / Participant)
    public function show($id): JsonResponse
    {
        $course = Course::with([
            'categorie',
            'sousCategorie',
            'evenement',
            'avertissement',
            'options.quantifiable',
            'options.cochable'
        ])->find($id);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        if ($course->evenement && $course->evenement->logo) {
            $course->evenement->logo = 'data:image/jpeg;base64,' . base64_encode($course->evenement->logo);
        }

        return response()->json($course, 200);
    }

    // POST (Admin)
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_evenement'      => 'required|integer|exists:Evenement,id',
            'id_categorie'      => 'nullable|integer|exists:Categorie,id',
            'id_sous_categorie' => 'nullable|integer|exists:SousCategorie,id',
            'id_avertissement'  => 'nullable|integer|exists:Avertissement,id',
            'nom'               => 'required|string|max:120',
            'date_debut'        => 'required|date',
            'date_fin'          => 'required|date',
            'debut_inscription' => 'required|date',
            'fin_inscription'   => 'required|date|after_or_equal:debut_inscription',
            'tarif'             => 'required|numeric|min:0',
            'status'            => 'required|string|max:50',
            'type'              => 'required|string|max:50',
            'max_inscription'   => 'required|integer|min:1',
            'distance'          => 'nullable|numeric|min:0',
            'premier_dossard'   => 'required|integer|min:1',
            'dernier_dossard'   => 'required|integer|gte:premier_dossard',
            'heure_depart'      => 'nullable|string',
            'heure_fin'         => 'nullable|string',
            'age_minimum'       => 'required|integer|min:0',
            'age_maximum'       => 'nullable|integer|gte:age_minimum',
            'is_challenge'      => 'boolean',
            'is_actif'          => 'boolean',
            'is_dossard'        => 'boolean',
            'is_avertissement'  => 'boolean',
            'is_document'       => 'boolean',
            'is_questionnaire'  => 'boolean',
        ]);

        $course = Course::create($validatedData);

        return response()->json([
            'message' => 'Course créée avec succès.',
            'course'  => $course
        ], 201);
    }

    // PUT (Admin)
    public function update(Request $request, $id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        $validatedData = $request->validate([
            'id_evenement'      => 'sometimes|required|integer|exists:Evenement,id',
            'id_categorie'      => 'nullable|integer|exists:Categorie,id',
            'id_sous_categorie' => 'nullable|integer|exists:SousCategorie,id',
            'id_avertissement'  => 'nullable|integer|exists:Avertissement,id',
            'nom'               => 'sometimes|required|string|max:120',
            'date_debut'        => 'sometimes|required|date',
            'date_fin'          => 'sometimes|required|date',
            'debut_inscription' => 'sometimes|required|date',
            'fin_inscription'   => 'sometimes|required|date|after_or_equal:debut_inscription',
            'tarif'             => 'sometimes|required|numeric|min:0',
            'max_inscription'   => 'sometimes|required|integer|min:1',
            'premier_dossard'   => 'sometimes|required|integer|min:1',
            'dernier_dossard'   => 'sometimes|required|integer|gte:premier_dossard',
            'distance'          => 'sometimes|required|numeric|min:0',
            'heure_depart'      => 'sometimes|nullable|string',
            'heure_fin'         => 'sometimes|nullable|string',
            'age_minimum'       => 'sometimes|required|integer|min:0',
            'age_maximum'       => 'sometimes|nullable|integer|gte:age_minimum',
            'is_challenge'      => 'boolean',
            'is_actif'          => 'boolean',
            'is_dossard'        => 'boolean',
            'is_avertissement'  => 'boolean',
            'is_document'       => 'boolean',
            'is_questionnaire'  => 'boolean',

        ]);

        $course->update($validatedData);

        return response()->json([
            'message' => 'Course mise à jour avec succès.',
            'course'  => $course
        ], 200);
    }

    // DELETE (Admin)
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