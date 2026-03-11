<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Récupérer toutes les questions
        $questions = DB::table('Question')->get();

        // 2. Garder uniquement la première occurrence de chaque énoncé
        $vus = [];
        $idsASupprimer = [];

        foreach ($questions as $question) {
            if (in_array($question->enonce, $vus)) {
                // Doublon — supprimer ses liens et la question
                $idsASupprimer[] = $question->id;
            } else {
                $vus[] = $question->enonce;
            }
        }

        if (!empty($idsASupprimer)) {
            // Supprimer les liens EvenementQuestion vers les doublons
            DB::table('CourseQuestion')
                ->whereIn('id_question', $idsASupprimer)
                ->delete();

            // Supprimer les options des questions doublons
            DB::table('OptionQuestion')
                ->whereIn('id_question', $idsASupprimer)
                ->delete();

            // Supprimer les questions doublons
            DB::table('Question')
                ->whereIn('id', $idsASupprimer)
                ->delete();
        }
    }

    public function down(): void
    {
        // Pas de rollback possible sur un nettoyage
    }
};
