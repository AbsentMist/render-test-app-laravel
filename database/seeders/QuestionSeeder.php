<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Evenement;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $evenements = [
            'Course des Ponts 2025',
            'Geneva Marathon 2025',
            'Nocturne des Evaux',
        ];

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

        // Compteur global d'ordre unique
        $ordreGlobal = 1;

        foreach ($evenements as $nomEvenement) {
            $evenement = Evenement::where('nom', $nomEvenement)->first();
            if (!$evenement) continue;

            foreach ($questions as $data) {
                $questionId = DB::table('Question')->insertGetId([
                    'enonce' => $data['enonce'],
                ]);

                foreach ($data['options'] as $texte) {
                    DB::table('OptionQuestion')->insert([
                        'id_question'  => $questionId,
                        'texte_option' => $texte,
                    ]);
                }

                DB::table('EvenementQuestion')->insert([
                    'id_evenement' => $evenement->id,
                    'id_question'  => $questionId,
                    'ordre'        => $ordreGlobal,
                ]);

                $ordreGlobal++;
            }
        }
    }
}