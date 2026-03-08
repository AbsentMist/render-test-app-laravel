<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Evenement;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 1. Créer les questions UNE SEULE FOIS =====
        $questions = [
            [
                'enonce'  => 'Comment avez-vous connu la course ?',
                'options' => ['Réseaux sociaux', 'Bouche à oreille', 'Journaux', 'Affichage'],
            ],
            [
                'enonce'  => 'Pour quelle raison souhaitez-vous participer à la course ?',
                'options' => ['Pour la forme', "Par but d'amélioration", "Par passion pour l'athlétisme"],
            ],
        ];

        $questionIds = [];

        foreach ($questions as $data) {
            // Créer la question seulement si elle n'existe pas déjà
            $question = DB::table('Question')
                ->where('enonce', $data['enonce'])
                ->first();

            if (!$question) {
                $questionId = DB::table('Question')->insertGetId([
                    'enonce' => $data['enonce'],
                ]);
            } else {
                $questionId = $question->id;
            }

            // Créer les options de réponse si elles n'existent pas
            foreach ($data['options'] as $texte) {
                $exists = DB::table('OptionQuestion')
                    ->where('id_question', $questionId)
                    ->where('texte_option', $texte)
                    ->exists();

                if (!$exists) {
                    DB::table('OptionQuestion')->insert([
                        'id_question'  => $questionId,
                        'texte_option' => $texte,
                    ]);
                }
            }

            $questionIds[] = $questionId;
        }

        // ===== 2. Lier les questions aux événements =====
        // Chaque événement repart de ordre 1 — propre et logique
        $evenements = [
            'Course des Ponts 2025',
            'Geneva Marathon 2025',
            'Nocturne des Evaux',
        ];

        foreach ($evenements as $nomEvenement) {
            $evenement = Evenement::where('nom', $nomEvenement)->first();
            if (!$evenement) continue;

            foreach ($questionIds as $ordre => $questionId) {
                // Vérifier si le lien existe déjà
                $lienExiste = DB::table('EvenementQuestion')
                    ->where('id_evenement', $evenement->id)
                    ->where('id_question', $questionId)
                    ->exists();

                if (!$lienExiste) {
                    DB::table('EvenementQuestion')->insert([
                        'id_evenement' => $evenement->id,
                        'id_question'  => $questionId,
                        'ordre'        => $ordre + 1, // ordre 1, 2 par événement
                    ]);
                }
            }
        }
    }
}