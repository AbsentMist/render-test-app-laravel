<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PrixEvolutif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_course');
            $table->enum('type', ['dossards', 'dates']);
            $table->string('valeur_debut', 20); // nb dossards OU date (YYYY-MM-DD)
            $table->string('valeur_fin', 20)->nullable(); // null = pas de limite haute
            $table->decimal('tarif', 8, 2);
            $table->integer('ordre')->default(1);

            $table->foreign('id_course')->references('id')->on('Course')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PrixEvolutif');
    }
};
