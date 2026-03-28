<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Document', function (Blueprint $table) {
            // Ajouter la colonne id_inscription
            $table->foreignId('id_inscription')->nullable()->after('id_participant')->constrained('Inscription')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('Document', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['id_inscription_foreign']);
            $table->dropColumn('id_inscription');
        });
    }
};
