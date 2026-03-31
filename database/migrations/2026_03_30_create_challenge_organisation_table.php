<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ChallengeOrganisation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_course');
            $table->string('nom', 100);
            $table->enum('type', ['Groupe', 'Entreprise']); // Groupe = étudiant, Entreprise = entreprise
            $table->foreign('id_course')->references('id')->on('Course')->onDelete('cascade');
            $table->unique(['id_course', 'nom', 'type'], 'challenge_org_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ChallengeOrganisation');
    }
};
