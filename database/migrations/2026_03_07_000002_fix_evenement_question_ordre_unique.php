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
            return; // On annule la migration si la table n'existe pas
        }

        // Suppression de l'ancien index sécurisée qui ne marchait pas sur MYSQL en production
        try {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS coursequestion_ordre_unique');
            } else {
                // Pour MySQL en production
                DB::statement('ALTER TABLE CourseQuestion DROP INDEX coursequestion_ordre_unique');
            }
        } catch (\Exception $e) {
            // SÉCURITÉ : Si l'index a déjà été supprimé, on ignore l'erreur pour ne pas bloquer le déploiement.
        }

        // On ajoute la nouvelle contrainte
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