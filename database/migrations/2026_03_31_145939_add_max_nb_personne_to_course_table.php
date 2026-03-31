<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Course', function (Blueprint $table) {
            $table->integer('max_nb_personne')->nullable()->after('max_inscription');
        });
    }

    public function down(): void
    {
        Schema::table('Course', function (Blueprint $table) {
            $table->dropColumn('max_nb_personne');
        });
    }
};