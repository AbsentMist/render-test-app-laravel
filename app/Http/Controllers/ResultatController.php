<?php

/**
 * @fileoverview ResultatController.php
 * @description Contrôleur gérant les résultats de course :
 *              consultation par participant ou par course, import depuis un fichier
 *              Excel fourni par le chronométreur et suppression pour réimport.
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\Resultat;
use App\Models\Dossard;
use App\Models\Course;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ResultatController extends Controller
{
    /**
     * Retourne les résultats de toutes les courses du participant connecté.
     * Chaque résultat est enrichi des informations de la course et de l'événement
     * (nom, date, couleurs, logo) pour l'affichage dans le tableau de bord participant.
     * @author Guillermet Jean-Daniel
     * @return \Illuminate\Http\JsonResponse Liste des résultats du participant connecté.
     */
    public function mesResultats()
    {
        $idParticipant = Auth::user()->participant->id;

        $resultats = Resultat::with([
            'inscription.course.evenement',
            'inscription.dossard',
        ])
            ->whereHas('inscription', function ($query) use ($idParticipant) {
                $query->where('id_participant', $idParticipant);
            })
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($resultat) {
                return [
                    'id'                 => $resultat->id,
                    'position'           => $resultat->position,
                    'temps_course'       => $resultat->temps_course,
                    'dossard'            => $resultat->inscription->dossard?->numero,
                    'course_nom'         => $resultat->inscription->course->nom,
                    'evenement_nom'      => $resultat->inscription->course->evenement->nom,
                    'date_course'        => $resultat->inscription->course->date_debut,
                    'couleur_primaire'   => $resultat->inscription->course->evenement->couleur_primaire,
                    'couleur_secondaire' => $resultat->inscription->course->evenement->couleur_secondaire,
                    'logo'               => $resultat->inscription->course->evenement->logo,
                ];
            });

        return response()->json($resultats);
    }

    /**
     * Retourne tous les résultats d'une course donnée, triés par position (vue organisateur).
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course.
     * @return \Illuminate\Http\JsonResponse Liste des résultats avec nom, prénom, dossard et temps.
     */
    public function indexParCourse($id_course)
    {
        Course::findOrFail($id_course);

        $resultats = Resultat::with([
            'inscription.participant',
            'inscription.dossard',
        ])
            ->whereHas('inscription', function ($query) use ($id_course) {
                $query->where('id_course', $id_course);
            })
            ->orderBy('position')
            ->get()
            ->map(function ($r) {
                return [
                    'id'           => $r->id,
                    'position'     => $r->position,
                    'temps_course' => $r->temps_course,
                    'dossard'      => $r->inscription->dossard?->numero,
                    'nom'          => $r->inscription->participant->nom,
                    'prenom'       => $r->inscription->participant->prenom,
                ];
            });

        return response()->json($resultats);
    }

    /**
     * Importe les résultats d'une course depuis un fichier Excel fourni par le chronométreur.
     * Calcule automatiquement les positions en triant par temps croissant.
     * Les participants sans temps (DNS/DNF) reçoivent une position null et sont placés en fin de liste.
     * Les dossards introuvables dans la base sont ignorés et comptabilisés séparément.
     * Un appel répété remplace les résultats existants (updateOrCreate).
     *
     * Format attendu du fichier :
     *   Colonnes : Dos | Nom Prénom | MF | Cat | Nat | Arrivée | TitreChallenge | Equipe
     *   Lignes "section" (ex: "10KM") → ignorées (valeur non numérique en colonne 0)
     *   Lignes sans temps → position null (DNS/DNF)
     *   Dossards > 100 000 → ignorés (dossards de test)
     *
     * @author Guillermet Jean-Daniel
     * @param  Request $request  Doit contenir le fichier `fichier` (xlsx, xls ou csv).
     * @param  int     $id_course Identifiant de la course cible.
     * @return \Illuminate\Http\JsonResponse Résumé de l'import : nombre importés et ignorés.
     */
    public function import(Request $request, $id_course)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $course      = Course::findOrFail($id_course);
        $fichier     = $request->file('fichier');
        $spreadsheet = IOFactory::load($fichier->getPathname());
        $feuille     = $spreadsheet->getActiveSheet();
        $lignes      = $feuille->toArray(null, true, true, false);

        // Collecte des données valides depuis le fichier
        $donnees      = [];
        $enteteIgnore = false;

        foreach ($lignes as $ligne) {
            $colonne0 = trim((string) ($ligne[0] ?? ''));

            // Ignore la ligne d'en-tête repérée par "Dos" en première colonne
            if (strtolower($colonne0) === 'dos') {
                $enteteIgnore = true;
                continue;
            }

            if (!$enteteIgnore) continue;

            // Ignore les lignes vides
            if (empty($colonne0)) continue;

            // Ignore les lignes de section (ex: "10KM") qui ne contiennent pas un numéro de dossard
            if (!is_numeric($colonne0)) continue;

            $numeroDossard = (int) $colonne0;

            // Ignore les dossards de test dont le numéro dépasse 100 000
            if ($numeroDossard > 100000) continue;

            // Colonne 5 = temps d'arrivée ; null si absent (DNS/DNF)
            $tempsRaw = trim((string) ($ligne[5] ?? ''));
            $temps    = $this->normaliserTemps($tempsRaw);

            $donnees[] = [
                'dossard' => $numeroDossard,
                'temps'   => $temps,
            ];
        }

        if (empty($donnees)) {
            return response()->json([
                'message' => 'Aucune donnée valide trouvée dans le fichier.'
            ], 422);
        }

        // Trie par temps croissant pour l'attribution des positions.
        // Les participants sans temps (null) sont repoussés en fin de liste.
        usort($donnees, function ($a, $b) {
            if ($a['temps'] === null && $b['temps'] === null) return 0;
            if ($a['temps'] === null) return 1;
            if ($b['temps'] === null) return -1;
            return strcmp($a['temps'], $b['temps']);
        });

        $importes = 0;
        $ignores  = 0;
        $position = 1;

        foreach ($donnees as $donnee) {
            // Recherche le dossard en base pour cette course spécifique
            $dossard = Dossard::whereHas('inscription', function ($query) use ($id_course) {
                $query->where('id_course', $id_course);
            })->where('numero', $donnee['dossard'])->first();

            if (!$dossard) {
                $ignores++;
                continue;
            }

            // Les participants sans temps n'ont pas de position (DNS/DNF)
            $positionFinale = $donnee['temps'] !== null ? $position++ : null;

            // Met à jour si un résultat existe déjà, crée sinon (idempotent)
            Resultat::updateOrCreate(
                ['id_inscription' => $dossard->id_inscription],
                [
                    'temps_course' => $donnee['temps'],
                    'position'     => $positionFinale,
                ]
            );

            $importes++;
        }

        return response()->json([
            'message'  => "Import terminé : {$importes} résultat(s) importé(s), {$ignores} dossard(s) non trouvé(s) dans cette course.",
            'importes' => $importes,
            'ignores'  => $ignores,
        ]);
    }

    /**
     * Supprime tous les résultats d'une course pour permettre une réimportation propre.
     * @author Guillermet Jean-Daniel
     * @param  int $id_course Identifiant de la course dont les résultats sont à effacer.
     * @return \Illuminate\Http\JsonResponse Nombre de résultats supprimés.
     */
    public function destroy($id_course)
    {
        Course::findOrFail($id_course);

        $supprime = Resultat::whereHas('inscription', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })->delete();

        return response()->json([
            'message'  => "{$supprime} résultat(s) supprimé(s).",
            'supprime' => $supprime,
        ]);
    }

    /**
     * Normalise un temps brut issu du fichier Excel au format HH:MM:SS.
     * Gère deux formats en entrée :
     *   - MM:SS  (ex: "37:39"   → "00:37:39")
     *   - HH:MM:SS (ex: "1:09:35" → "01:09:35")
     * Retourne null si la chaîne est vide ou dans un format non reconnu.
     * @author Guillermet Jean-Daniel
     * @param  string $tempsRaw Temps brut lu depuis la cellule Excel.
     * @return string|null Temps normalisé au format HH:MM:SS ou null.
     */
    private function normaliserTemps(string $tempsRaw): ?string
    {
        if (empty($tempsRaw)) return null;

        $parties = explode(':', $tempsRaw);

        if (count($parties) === 2) {
            // Format MM:SS → converti en HH:MM:SS en ajoutant "00" pour les heures
            return sprintf('00:%02d:%02d', (int) $parties[0], (int) $parties[1]);
        }

        if (count($parties) === 3) {
            // Format HH:MM:SS → normalisation du zero-padding
            return sprintf('%02d:%02d:%02d', (int) $parties[0], (int) $parties[1], (int) $parties[2]);
        }

        return null;
    }
}