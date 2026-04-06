<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Course', function (Blueprint $table) {
            $table->boolean('is_prix_evolutif')->default(false)->after('is_questionnaire');
        });
    }

    public function down(): void
    {
        Schema::table('Course', function (Blueprint $table) {
            $table->dropColumn('is_prix_evolutif');
        });
    }
};
