<?php

/**
 * @fileoverview EvenementController.php
 * @description Contrôleur REST gérant les événements sportifs :
 *              lecture (admin/participant), création, modification, suppression
 *              et gestion de l'ordre d'affichage sur la page d'accueil.
 *              Le logo est stocké en BLOB en base de données et converti en base64 à la lecture.
 * @author Ngoie Steven
 */

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    /**
     * Retourne tous les événements pour la vue administrateur, triés par ordre d'affichage.
     * Chaque événement est enrichi d'une propriété `prochaine_date` calculée depuis
     * la date de fin d'inscription la plus proche parmi ses courses.
     * L'ordre d'affichage prioritise le champ `ordre` (épinglage manuel), puis la prochaine date,
     * puis place les événements sans date en dernier.
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (calcul de prochaine_date et tri par ordre/date)
     * @return \Illuminate\Http\JsonResponse Liste de tous les événements triés.
     */
    public function indexAdmin()
    {
        $evenements = Evenement::with('courses')
            ->get()
            ->map(function ($evenement) {
                // Convertit le logo binaire en base64 pour l'affichage dans le frontend
                if ($evenement->logo) {
                    $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
                }
                // Calcule la prochaine date d'inscription à partir de la course dont la fin est la plus proche
                $evenement->prochaine_date = $evenement->courses
                    ->whereNotNull('fin_inscription')
                    ->sortBy('fin_inscription')
                    ->first()?->fin_inscription;
                return $evenement;
            })
            // Tri : ordre manuel en premier, puis par prochaine date, puis sans date à la fin
            ->sortBy(function ($e) {
                if ($e->ordre !== null) return $e->ordre;
                if ($e->prochaine_date) return 1000 + strtotime($e->prochaine_date);
                return PHP_INT_MAX;
            })
            ->values();

        return response()->json($evenements);
    }

    /**
     * Sauvegarde l'ordre d'affichage des événements épinglés sur la page d'accueil.
     * Un ordre `null` signifie que l'événement n'est pas épinglé et sera trié automatiquement.
     * @author Guillermet Jean-Daniel
     * @param  \Illuminate\Http\Request $request Tableau d'objets `{id, ordre}` pour chaque événement.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function updateOrdre(Request $request)
    {
        $request->validate([
            'evenements'          => 'required|array',
            'evenements.*.id'     => 'required|exists:Evenement,id',
            'evenements.*.ordre'  => 'nullable|integer|min:1',
        ]);

        foreach ($request->evenements as $item) {
            Evenement::where('id', $item['id'])->update(['ordre' => $item['ordre']]);
        }

        return response()->json(['message' => 'Ordre mis à jour avec succès.']);
    }

    /**
     * Retourne les événements actifs pour la vue participant, triés par ordre d'affichage.
     * Seuls les champs nécessaires à l'affichage de la liste sont chargés pour alléger la réponse.
     * La propriété `prochaine_date` est calculée de la même façon que dans indexAdmin.
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (champ ordre, chargement minimal des courses, calcul prochaine_date et tri)
     * @return \Illuminate\Http\JsonResponse Liste des événements actifs triés.
     */
    public function indexParticipant()
    {
        $evenements = Evenement::where('is_actif', true)
            // Sélection minimale des champs pour alléger la réponse côté participant
            ->select('id', 'nom', 'logo', 'site', 'couleur_primaire', 'couleur_secondaire', 'ordre')
            ->with(['courses' => function ($q) {
                // Charge uniquement les champs nécessaires au calcul de prochaine_date
                $q->select('id', 'id_evenement', 'fin_inscription');
            }])
            ->get()
            ->map(function ($evenement) {
                // Convertit le logo binaire en base64 pour l'affichage dans le frontend
                if ($evenement->logo) {
                    $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
                }
                // Calcule la prochaine date d'inscription à partir de la course dont la fin est la plus proche
                $evenement->prochaine_date = $evenement->courses
                    ->whereNotNull('fin_inscription')
                    ->sortBy('fin_inscription')
                    ->first()?->fin_inscription;
                return $evenement;
            })
            // Tri : ordre manuel en premier, puis par prochaine date, puis sans date à la fin
            ->sortBy(function ($e) {
                if ($e->ordre !== null) return $e->ordre;
                if ($e->prochaine_date) return 1000 + strtotime($e->prochaine_date);
                return PHP_INT_MAX;
            })
            ->values();

        return response()->json($evenements);
    }

    /**
     * Crée un nouvel événement (vue administrateur).
     * Si un logo est fourni, il est converti en BLOB avant d'être stocké en base de données.
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (validation du logo en tant que fichier image)
     * @author Neris Alessandro (message de validation personnalisé pour le champ nom)
     * @param  \Illuminate\Http\Request $request Données de l'événement à créer.
     * @return \Illuminate\Http\JsonResponse Événement créé (201).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom'               => 'required|string|max:180',
            'logo'              => 'nullable|file|image|max:2048',
            'site'              => 'nullable|string|max:255',
            'couleur_primaire'  => 'nullable|string|max:50',
            'couleur_secondaire'=> 'nullable|string|max:50',
            'is_avertissement'  => 'boolean',
            'is_document'       => 'boolean',
            'is_questionnaire'  => 'boolean',
            'is_rabais'         => 'boolean',
            'is_actif'          => 'boolean',
            'is_interne'        => 'boolean',
        ], [
            'nom.required' => "Le nom de l'évènement est requis."
        ]);

        // Convertit le fichier image en BLOB pour le stockage en base de données
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = file_get_contents($request->file('logo')->getRealPath());
        }

        $evenement = Evenement::create($validatedData);

        return response()->json([
            'message'   => 'Évènement créé avec succès.',
            'evenement' => $evenement
        ], 201);
    }

    /**
     * Retourne le détail d'un événement spécifique.
     * Le logo est converti en base64 si présent.
     * @author Ngoie Steven
     * @param  int $id Identifiant de l'événement.
     * @return \Illuminate\Http\JsonResponse Détail de l'événement ou 404 si introuvable.
     */
    public function show($id)
    {
        $evenement = Evenement::findOrFail($id);

        // Convertit le logo binaire en base64 pour l'affichage dans le frontend
        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        return response()->json($evenement);
    }

    /**
     * Met à jour un événement existant (vue administrateur).
     * Tous les champs sont optionnels. Si un nouveau logo est uploadé, il remplace l'ancien en BLOB.
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (validation du logo en tant que fichier image)
     * @param  \Illuminate\Http\Request $request Champs à mettre à jour.
     * @param  int                      $id      Identifiant de l'événement.
     * @return \Illuminate\Http\JsonResponse Événement mis à jour (200).
     */
    public function update(Request $request, $id)
    {
        $evenement = Evenement::findOrFail($id);

        $validatedData = $request->validate([
            'nom'               => 'sometimes|string|max:180',
            'logo'              => 'nullable|file|image|max:2048',
            'site'              => 'nullable|string|max:255',
            'couleur_primaire'  => 'nullable|string|max:50',
            'couleur_secondaire'=> 'nullable|string|max:50',
            'is_avertissement'  => 'boolean',
            'is_document'       => 'boolean',
            'is_questionnaire'  => 'boolean',
            'is_rabais'         => 'boolean',
            'is_actif'          => 'boolean',
            'is_interne'        => 'boolean',
        ]);

        // Convertit le fichier image en BLOB pour le stockage en base de données
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = file_get_contents($request->file('logo')->getRealPath());
        }

        $evenement->update($validatedData);

        return response()->json([
            'message'   => 'Évènement mis à jour avec succès.',
            'evenement' => $evenement
        ]);
    }

    /**
     * Supprime définitivement un événement (vue administrateur uniquement).
     * @author Ngoie Steven
     * @param  int $id Identifiant de l'événement à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);
        $evenement->delete();

        return response()->json([
            'message' => 'Évènement supprimé avec succès.'
        ]);
    }
}