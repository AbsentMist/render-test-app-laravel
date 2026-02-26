<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('Participant', function (Blueprint $table) {
        $table->id();

        $table->foreignId('id_user')->constrained('User')->onDelete('cascade');
        $table->string('nom', 100);
        $table->string('prenom', 100);
        $table->date('date_naissance');
        $table->string('equipe_nom', 100)->nullable();
        $table->string('adresse', 100);
        $table->string('code_postal', 10);
        $table->string('ville', 100);
        $table->string('pays', 100);
        $table->string('telephone', 20)->unique();
        $table->string('nationalite', 100);
        $table->string('instagram', 255)->nullable();
        $table->string('facebook', 255)->nullable();
        $table->string('taille_tshirt', 10);
        $table->string('sexe', 10);
        $table->binary('photo')->nullable();
    });
}

public function down(): void
{
    Schema::dropIfExists('Participant');
}
};
