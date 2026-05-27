<?php

/**
 * @fileoverview CourseController.php
 * @description Contrôleur REST gérant les courses rattachées à un événement :
 *              lecture publique/admin, création, modification et suppression.
 *              Inclut le calcul dynamique du tarif évolutif et le formatage
 *              du questionnaire pour le frontend.
 * @author Ngoie Steven
 */

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * Retourne les courses actives et ouvertes aux inscriptions pour un événement donné.
     * Charge toutes les relations nécessaires au formulaire d'inscription (options, questionnaire,
     * prix évolutifs) et calcule le tarif applicable en fonction du nombre d'inscrits courant.
     * @author Ngoie Steven
     * @author Neris Alessandro (eager loading, formatage questionnaire, options)
     * @author Guillermet Jean-Daniel (calcul du tarif évolutif, chargement questions/choix et prixEvolutifs)
     * @param  int $id_evenement Identifiant de l'événement parent.
     * @return JsonResponse Événement avec ses courses actives et ouvertes, enrichies de toutes leurs relations.
     */
    public function indexParticipant($id_evenement): JsonResponse
    {
        // Charge uniquement les champs nécessaires pour alléger la réponse
        $evenement = Evenement::select('id', 'nom', 'logo', 'couleur_primaire', 'couleur_secondaire')
            ->findOrFail($id_evenement);

        // Convertit le logo binaire en base64 pour l'affichage dans le frontend
        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        // Eager Loading de toutes les relations nécessaires au formulaire d'inscription
        $courses = Course::with([
                'categorie',
                'sousCategorie',
                'evenement',
                'avertissement',
                'options.quantifiable',
                'options.cochable',
                'questions.choix',   // Charge les questions ET leurs choix de réponses associés
                'prixEvolutifs',
                ])
            ->withCount('inscriptions')
            ->where('id_evenement', $id_evenement)
            ->where('is_actif', true)
            // Filtre sur la fenêtre d'inscription : cours sans limite ou dont la limite n'est pas encore dépassée
            ->where(function($query) {
                $query->whereNull('fin_inscription')             // Pas de date limite définie
                      ->orWhere('fin_inscription', '>=', now()->toDateString()); // Ou date limite aujourd'hui ou future
            })
            ->get()
            ->map(function ($course) {
                return [
                    'id'                => $course->id,
                    'nom_course'        => $course->nom,
                    // Calcule le tarif applicable : palier évolutif si activé, tarif fixe sinon
                    'tarif' => $course->is_prix_evolutif
                        ? (function() use ($course) {
                            // Simule la position du prochain inscrit pour déterminer son palier
                            $nbInscrits = $course->inscriptions_count + 1;
                            $palier = $course->prixEvolutifs->sortBy('ordre')->first(function($p) use ($nbInscrits) {
                                $debut = (int) $p->valeur_debut;
                                $fin = $p->valeur_fin !== null ? (int) $p->valeur_fin : PHP_INT_MAX;
                                return $nbInscrits >= $debut && $nbInscrits <= $fin;
                            });
                            // Fallback sur le tarif de base si aucun palier ne correspond
                            return $palier?->tarif ?? $course->tarif;
                        })()
                        : $course->tarif,
                    'type'              => $course->type,
                    'is_challenge'      => $course->is_challenge,
                    'is_prix_evolutif'  => $course->is_prix_evolutif,
                    'document_description' => $course->document_description,
                    'categorie'         => $course->categorie->nom ?? null,
                    'sous_categorie'    => $course->sousCategorie->nom ?? null,
                    'avertissement'     => $course->avertissement,
                    'options'           => $course->options,
                    'document'          => $course->is_document,
                    'evenement'         => $course->evenement,
                    // Formate le questionnaire uniquement si la course en possède un
                    'questionnaire'     => $course->is_questionnaire ? $course->questions->map(function($q) {
                        return [
                            'id'       => $q->id,
                            'question' => $q->enonce,
                            'answers'  => $q->choix->map(function($choix) {
                                return [
                                    'id'    => $choix->id,
                                    'texte' => $choix->texte_option,
                                ];
                            }),
                        ];
                    }) : null,
                    // Calcule les dossards restants ou indique "Illimité" si aucun max n'est défini
                    'dossards_restants' => $course->max_inscription
                        ? ($course->max_inscription - $course->inscriptions_count)
                        : 'Illimité',
                    'max_nb_personne' => $course->max_nb_personne,
                    'age_minimum'     => $course->age_minimum,
                    'age_maximum'     => $course->age_maximum,
                    'date_debut'      => $course->date_debut,
                    'date_fin'        => $course->date_fin,
                ];
            });

        return response()->json([
            'evenement' => $evenement,
            'courses'   => $courses
        ], 200);
    }

    /**
     * Retourne toutes les courses d'un événement pour la vue administrateur.
     * Contrairement à la vue participant, aucun filtre sur is_actif ou fin_inscription n'est appliqué :
     * l'admin voit toutes les courses y compris les inactives ou clôturées.
     * @author Ngoie Steven
     * @param  int $id_evenement Identifiant de l'événement parent.
     * @return JsonResponse Événement avec toutes ses courses et le nombre d'inscriptions par course.
     */
    public function indexAdmin($id_evenement): JsonResponse
    {
        $evenement = Evenement::find($id_evenement);

        if (!$evenement) {
            return response()->json(['message' => 'Événement introuvable.'], 404);
        }

        // Convertit le logo binaire en base64 pour l'affichage dans le frontend
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

    /**
     * Retourne le détail complet d'une course avec toutes ses relations.
     * Accessible à l'administrateur et au participant.
     * Le questionnaire est reformaté dans une structure normalisée pour le frontend.
     * @author Neris Alessandro
     * @param  int $id Identifiant de la course.
     * @return JsonResponse Détail de la course ou 404 si introuvable.
     */
    public function show($id): JsonResponse
    {
        $course = Course::with([
            'categorie',
            'sousCategorie',
            'evenement',
            'avertissement',
            'options.quantifiable',
            'options.cochable',
            'questions.choix'
        ])->find($id);

        if (!$course) {
            return response()->json(['message' => 'Course introuvable.'], 404);
        }

        // Convertit le logo de l'événement parent en base64 pour le frontend
        if ($course->evenement && $course->evenement->logo) {
            $course->evenement->logo = 'data:image/jpeg;base64,' . base64_encode($course->evenement->logo);
        }

        // Formate les questions en structure normalisée {id, question, answers[]}
        // ou null si la course ne possède pas de questionnaire
        if ($course->is_questionnaire && $course->questions) {
            $course->questionnaire = $course->questions->map(function($q) {
                return [
                    'id'       => $q->id,
                    'question' => $q->enonce,
                    'answers'  => $q->choix->map(function($choix) {
                        return [
                            'id'    => $choix->id,
                            'texte' => $choix->texte_option,
                        ];
                    }),
                ];
            })->values();
        } else {
            $course->questionnaire = null;
        }

        return response()->json($course, 200);
    }

    /**
     * Crée une nouvelle course et l'associe à un événement existant.
     * Les règles de validation garantissent la cohérence des dates et des bornes de dossard.
     * @author Ngoie Steven
     * @author Neris Alessandro (champs booléens, max_nb_personne, document_description, is_prix_evolutif)
     * @author Guillermet Jean-Daniel (validation des dates d'inscription, tarif nullable)
     * @param  Request $request Données de la course à créer.
     * @return JsonResponse Course créée (201).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_evenement'      => 'required|integer|exists:Evenement,id',
            'id_categorie'      => 'nullable|integer|exists:Categorie,id',
            'id_sous_categorie' => 'nullable|integer|exists:SousCategorie,id',
            'id_avertissement'  => 'nullable|integer|exists:Avertissement,id',
            'nom'               => 'required|string|max:120',
            // Les dates de course et d'inscription doivent être cohérentes entre elles
            'date_debut'        => 'required|date|after_or_equal:today',
            'date_fin'          => 'required|date|after_or_equal:date_debut',
            'debut_inscription' => 'required|date|after_or_equal:today',
            'fin_inscription'   => 'required|date|after_or_equal:debut_inscription|before_or_equal:date_debut',
            'tarif'             => 'nullable|numeric|min:0',
            'status'            => 'required|string|max:50',
            'type'              => 'required|string|max:50',
            'max_inscription'   => 'required|integer|min:1',
            'max_nb_personne'   => 'nullable|integer|min:1',
            'distance'          => 'nullable|numeric|min:0',
            'premier_dossard'   => 'required|integer|min:1',
            'dernier_dossard'   => 'required|integer|gte:premier_dossard',
            'age_minimum'       => 'required|integer|min:0',
            'age_maximum'       => 'nullable|integer|gte:age_minimum',
            'is_challenge'      => 'boolean',
            'is_actif'          => 'boolean',
            'is_dossard'        => 'boolean',
            'is_avertissement'  => 'boolean',
            'is_document'       => 'boolean',
            'is_questionnaire'  => 'boolean',
            'is_prix_evolutif'  => 'boolean',
            'document_description' => 'nullable|string',
        ]);

        $course = Course::create($validatedData);

        return response()->json([
            'message' => 'Course créée avec succès.',
            'course'  => $course
        ], 201);
    }

    /**
     * Met à jour une course existante (vue administrateur).
     * Tous les champs sont optionnels (PATCH-like) : seuls les champs fournis sont modifiés.
     * @author Ngoie Steven
     * @author Neris Alessandro (champs booléens, max_nb_personne, heure_depart/fin, age_maximum, type)
     * @author Guillermet Jean-Daniel (is_prix_evolutif)
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant de la course.
     * @return JsonResponse Course mise à jour (200) ou 404 si introuvable.
     */
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
            'max_nb_personne'   => 'sometimes|nullable|integer|min:1',
            'premier_dossard'   => 'sometimes|required|integer|min:1',
            'dernier_dossard'   => 'sometimes|required|integer|gte:premier_dossard',
            'distance'          => 'sometimes|required|numeric|min:0',
            'heure_depart'      => 'sometimes|nullable|string',
            'heure_fin'         => 'sometimes|nullable|string',
            'age_minimum'       => 'sometimes|required|integer|min:0',
            'age_maximum'       => 'sometimes|nullable|integer|gte:age_minimum',
            'type'              => 'sometimes|required|string',
            'is_challenge'      => 'boolean',
            'is_prix_evolutif'  => 'boolean',
            'is_actif'          => 'boolean',
            'is_dossard'        => 'boolean',
            'is_avertissement'  => 'boolean',
            'is_document'       => 'boolean',
            'is_questionnaire'  => 'boolean',
            'document_description' => 'nullable|string',
        ]);

        $course->update($validatedData);

        return response()->json([
            'message' => 'Course mise à jour avec succès.',
            'course'  => $course
        ], 200);
    }

    /**
     * Supprime définitivement une course (vue administrateur).
     * @author Ngoie Steven
     * @param  int $id Identifiant de la course à supprimer.
     * @return JsonResponse Message de confirmation (200) ou 404 si introuvable.
     */
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