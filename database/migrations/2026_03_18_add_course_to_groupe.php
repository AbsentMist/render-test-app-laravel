<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Groupe', function (Blueprint $table) {
            $table->unsignedBigInteger('id_course')->nullable()->after('type');
            $table->foreign('id_course')->references('id')->on('Course')->onDelete('cascade');
            $table->dropUnique('groupe_nom_unique');
            $table->unique(['nom', 'id_course'], 'groupe_nom_course_unique');
        });
    }

    public function down(): void
    {
        Schema::table('Groupe', function (Blueprint $table) {
            $table->dropForeign(['id_course']);
            $table->dropUnique('groupe_nom_course_unique');
            $table->dropColumn('id_course');
            $table->unique('nom', 'groupe_nom_unique');
        });
    }
};