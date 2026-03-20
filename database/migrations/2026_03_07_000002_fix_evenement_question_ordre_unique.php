<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SÉCURITÉ ABSOLUE : On vérifie si la table existe avant de la modifier
        if (!Schema::hasTable('CourseQuestion')) {
            return; // On annule silencieusement la migration si la table est introuvable
        }

        // 1. On supprime l'index s'il existe (pour SQLite)
        DB::statement('DROP INDEX IF EXISTS "coursequestion_ordre_unique"');

        // 2. On ajoute la nouvelle contrainte
        Schema::table('CourseQuestion', function (Blueprint $table) {
            $table->unique(['id_course', 'ordre'], 'coursequestion_course_ordre_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('CourseQuestion')) {
            return;
        }

        Schema::table('CourseQuestion', function (Blueprint $table) {
            $table->dropUnique('coursequestion_course_ordre_unique');
            $table->unique(['ordre'], 'coursequestion_ordre_unique');
        });
    }
};