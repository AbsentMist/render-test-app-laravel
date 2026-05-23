<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'enonce'  => 'Comment avez-vous connu cet événement ?',
                'options' => ['Réseaux sociaux', 'Bouche à oreille', 'Affichage', 'Presse / journaux', 'Site web'],
            ],
            [
                'enonce'  => 'Pour quelle raison participez-vous ?',
                'options' => ['Pour le plaisir', "Pour me dépasser", "Par passion pour la course", 'Pour une bonne cause', 'Sur invitation'],
            ],
            [
                'enonce'  => 'Quel est votre niveau de pratique ?',
                'options' => ['Débutant', 'Intermédiaire', 'Confirmé', 'Compétiteur'],
            ],
        ];

        $questionIds = [];
        foreach ($questions as $data) {
            DB::table('Question')->updateOrInsert(
                ['enonce' => $data['enonce']],
                ['enonce' => $data['enonce'], 'modele' => true]
            );

            $question = DB::table('Question')->where('enonce', $data['enonce'])->first();

            foreach ($data['options'] as $texte) {
                DB::table('OptionQuestion')->updateOrInsert(
                    ['id_question' => $question->id, 'texte_option' => $texte]
                );
            }

            $questionIds[] = $question->id;
        }

        // Lier les questions aux courses qui ont is_questionnaire = true
        $coursesAvecQuestionnaire = [
            '10km des Ponts',
            'Antigel Night Run 10km',
            'Marathon de Genève',
            'Nocturne Relais 2x5km',
        ];

        foreach ($coursesAvecQuestionnaire as $nomCourse) {
            $course = Course::where('nom', $nomCourse)->first();
            if (!$course) continue;

            foreach ($questionIds as $ordre => $questionId) {
                DB::table('CourseQuestion')->updateOrInsert(
                    ['id_course' => $course->id, 'id_question' => $questionId],
                    ['ordre' => $ordre + 1]
                );
            }
        }
    }
}