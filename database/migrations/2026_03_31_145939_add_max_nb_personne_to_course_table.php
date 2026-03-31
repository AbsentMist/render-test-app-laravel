<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Correction : on n'ajoute la colonne que si elle n'existe pas déjà
        if (!Schema::hasColumn('Course', 'max_nb_personne')) {
            Schema::table('Course', function (Blueprint $table) {
                $table->integer('max_nb_personne')->nullable()->after('max_inscription');
            });
        }
    }

    public function down(): void
    {
        // Correction : on ne la supprime que si elle existe
        if (Schema::hasColumn('Course', 'max_nb_personne')) {
            Schema::table('Course', function (Blueprint $table) {
                $table->dropColumn('max_nb_personne');
            });
        }
    }
};