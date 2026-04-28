<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CodeRabais', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            // 'pourcentage' = réduction en % | 'montant_fixe' = réduction en CHF
            $table->enum('type', ['pourcentage', 'montant_fixe']);
            $table->double('valeur'); // ex: 50 pour 50% ou 10 pour 10 CHF
            $table->foreignId('id_course')->nullable()->constrained('Course')->nullOnDelete();
            $table->integer('utilisations_max')->nullable(); // null = illimité
            $table->integer('utilisations_actuelles')->default(0);
            $table->date('date_expiration')->nullable();
            $table->tinyInteger('actif')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CodeRabais');
    }
};
