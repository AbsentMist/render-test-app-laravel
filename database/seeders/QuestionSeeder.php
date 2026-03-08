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

        $ordreGlobal = 1;

        foreach ($evenements as $nomEvenement) {
            $evenement = Evenement::where('nom', $nomEvenement)->first();
            if (!$evenement) continue;

            foreach ($questions as $data) {
                // Créer ou retrouver la question par son énoncé
                $questionId = DB::table('Question')
                    ->where('enonce', $data['enonce'])
                    ->value('id');

                if (!$questionId) {
                    $questionId = DB::table('Question')->insertGetId([
                        'enonce' => $data['enonce'],
                    ]);
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

                // Lier la question à l'événement si pas déjà lié
                $lienExiste = DB::table('EvenementQuestion')
                    ->where('id_evenement', $evenement->id)
                    ->where('id_question', $questionId)
                    ->exists();

                if (!$lienExiste) {
                    DB::table('EvenementQuestion')->insert([
                        'id_evenement' => $evenement->id,
                        'id_question'  => $questionId,
                        'ordre'        => $ordreGlobal,
                    ]);
                }

                $ordreGlobal++;
            }
        }
    }
}