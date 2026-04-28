<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CodeDossard', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('nom_personnalise', 150)->nullable(); // Nom affiché sur le dossard
            $table->foreignId('id_course')->constrained('Course')->cascadeOnDelete();
            $table->integer('utilisations_max');             // Nb de participants pouvant utiliser ce code
            $table->integer('utilisations_actuelles')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CodeDossard');
    }
};
