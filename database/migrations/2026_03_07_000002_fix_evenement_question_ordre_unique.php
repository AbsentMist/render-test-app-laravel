<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CourseQuestion', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique globale sur ordre
            $table->dropUnique(['ordre']);

            // Ajouter la nouvelle contrainte unique par événement
            $table->unique(['id_course', 'ordre'], 'coursequestion_course_ordre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('CourseQuestion', function (Blueprint $table) {
            $table->dropUnique('coursequestion_course_ordre_unique');
            $table->unique(['ordre']);
        });
    }
};
