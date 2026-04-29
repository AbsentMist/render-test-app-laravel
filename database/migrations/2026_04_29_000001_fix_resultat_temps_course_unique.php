<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Supprime la contrainte UNIQUE sur temps_course
            // Deux coureurs peuvent avoir le même temps → cette contrainte est un bug
            Schema::table('Resultat', function (Blueprint $table) {
                $table->dropUnique('resultat_temps_course_unique');
            });
        }
        // SQLite : pas de contrainte UNIQUE sur ce champ, rien à faire
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::table('Resultat', function (Blueprint $table) {
                $table->unique('temps_course', 'resultat_temps_course_unique');
            });
        }
    }
};
