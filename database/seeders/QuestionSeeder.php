<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Evenement;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
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
            // updateOrCreate — comme les autres seeders
            $questionId = DB::table('Question')
                ->updateOrInsert(
                    ['enonce' => $data['enonce']]
                );

            $question = DB::table('Question')->where('enonce', $data['enonce'])->first();

            foreach ($data['options'] as $texte) {
                DB::table('OptionQuestion')->updateOrInsert(
                    ['id_question' => $question->id, 'texte_option' => $texte]
                );
            }

            $questionIds[] = $question->id;
        }

        // Lier les questions aux événements
        $evenements = ['Course des Ponts 2025', 'Geneva Marathon 2025', 'Nocturne des Evaux'];

        foreach ($evenements as $nomEvenement) {
            $evenement = Evenement::where('nom', $nomEvenement)->first();
            if (!$evenement) continue;

            foreach ($questionIds as $ordre => $questionId) {
                DB::table('EvenementQuestion')->updateOrInsert(
                    ['id_evenement' => $evenement->id, 'id_question' => $questionId],
                    ['ordre' => $ordre + 1]
                );
            }
        }
    }
}