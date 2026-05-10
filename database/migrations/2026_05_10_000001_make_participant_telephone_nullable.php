<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Participant', function (Blueprint $table) {
            $table->dropUnique(['telephone']);
            $table->string('telephone', 20)->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('Participant', function (Blueprint $table) {
            $table->dropUnique(['telephone']);
            $table->string('telephone', 20)->unique()->change();
        });
    }
};