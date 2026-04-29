<?php

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
     * GET /participant/resultats
     * Retourne les résultats du participant connecté.
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
                    'id'             => $resultat->id,
                    'position'       => $resultat->position,
                    'temps_course'   => $resultat->temps_course,
                    'dossard'        => $resultat->inscription->dossard?->numero,
                    'course_nom'     => $resultat->inscription->course->nom,
                    'evenement_nom'  => $resultat->inscription->course->evenement->nom,
                    'date_course'    => $resultat->inscription->course->date_debut,
                    'couleur_primaire'   => $resultat->inscription->course->evenement->couleur_primaire,
                    'couleur_secondaire' => $resultat->inscription->course->evenement->couleur_secondaire,
                    'logo'               => $resultat->inscription->course->evenement->logo,
                ];
            });

        return response()->json($resultats);
    }

    /**
     * GET /organisateur/courses/{id_course}/resultats
     * Retourne tous les résultats d'une course (vue organisateur).
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
     * POST /organisateur/courses/{id_course}/resultats/import
     * Import des résultats depuis un fichier Excel fourni par le chronométreur.
     *
     * Format attendu du fichier :
     *   Colonnes : Dos | Nom Prénom | MF | Cat | Nat | Arrivée | TitreChallenge | Equipe
     *   Lignes "section" (ex: "10KM") → ignorées
     *   Lignes sans temps → position null (DNS/DNF)
     */
    public function import(Request $request, $id_course)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $course = Course::findOrFail($id_course);

        $fichier = $request->file('fichier');
        $spreadsheet = IOFactory::load($fichier->getPathname());
        $feuille = $spreadsheet->getActiveSheet();
        $lignes = $feuille->toArray(null, true, true, false);

        // Collecter les données valides depuis le fichier
        $donnees = [];
        $enteteIgnore = false;

        foreach ($lignes as $ligne) {
            $colonne0 = trim((string) ($ligne[0] ?? ''));

            // Ignorer la ligne d'en-tête (contient "Dos")
            if (strtolower($colonne0) === 'dos') {
                $enteteIgnore = true;
                continue;
            }

            if (!$enteteIgnore) continue;

            // Ignorer les lignes vides
            if (empty($colonne0)) continue;

            // Ignorer les lignes "section" (ex: "10KM", "5KM") — pas un nombre
            if (!is_numeric($colonne0)) continue;

            $numeroDossard = (int) $colonne0;

            // Ignorer les dossards de test (> 100000)
            if ($numeroDossard > 100000) continue;

            $tempsRaw = trim((string) ($ligne[5] ?? ''));
            $temps = $this->normaliserTemps($tempsRaw);

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

        // Trier par temps croissant pour calculer les positions
        // Les participants sans temps (DNS/DNF) vont en fin de liste
        usort($donnees, function ($a, $b) {
            if ($a['temps'] === null && $b['temps'] === null) return 0;
            if ($a['temps'] === null) return 1;
            if ($b['temps'] === null) return -1;
            return strcmp($a['temps'], $b['temps']);
        });

        $importes  = 0;
        $ignores   = 0;
        $position  = 1;

        foreach ($donnees as $donnee) {
            // Retrouver le dossard dans la BDD pour cette course
            $dossard = Dossard::whereHas('inscription', function ($query) use ($id_course) {
                $query->where('id_course', $id_course);
            })->where('numero', $donnee['dossard'])->first();

            if (!$dossard) {
                $ignores++;
                continue;
            }

            $positionFinale = $donnee['temps'] !== null ? $position++ : null;

            // Créer ou mettre à jour le résultat
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
     * DELETE /organisateur/courses/{id_course}/resultats
     * Supprime tous les résultats d'une course (pour réimporter).
     */
    public function destroy($id_course)
    {
        Course::findOrFail($id_course);

        $supprime = Resultat::whereHas('inscription', function ($query) use ($id_course) {
            $query->where('id_course', $id_course);
        })->delete();

        return response()->json([
            'message'   => "{$supprime} résultat(s) supprimé(s).",
            'supprime'  => $supprime,
        ]);
    }

    /**
     * Normalise un temps au format HH:MM:SS.
     * Gère les formats : "37:39" → "00:37:39", "1:09:35" → "01:09:35"
     * @param string $tempsRaw
     * @return string|null
     */
    private function normaliserTemps(string $tempsRaw): ?string
    {
        if (empty($tempsRaw)) return null;

        $parties = explode(':', $tempsRaw);

        if (count($parties) === 2) {
            // MM:SS → HH:MM:SS
            return sprintf('00:%02d:%02d', (int) $parties[0], (int) $parties[1]);
        }

        if (count($parties) === 3) {
            // HH:MM:SS
            return sprintf('%02d:%02d:%02d', (int) $parties[0], (int) $parties[1], (int) $parties[2]);
        }

        return null;
    }
}
