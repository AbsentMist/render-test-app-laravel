<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('Document', 'id_inscription')) {
            return;
        }

        Schema::table('Document', function (Blueprint $table) {
            // Ajouter la colonne id_inscription
            $table->foreignId('id_inscription')->nullable()->after('id_participant')->constrained('Inscription')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('Document', 'id_inscription')) {
            return;
        }

        Schema::table('Document', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_inscription']);
            } catch (\Throwable $e) {
                // Ignore si la contrainte n'existe déjà plus.
            }
            $table->dropColumn('id_inscription');
        });
    }
};
