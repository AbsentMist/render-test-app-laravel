<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite ne supporte pas MODIFY COLUMN
            // On recrée la table avec position nullable
            DB::statement('CREATE TABLE "Resultat_new" (
                "id" integer PRIMARY KEY AUTOINCREMENT,
                "temps_course" time DEFAULT NULL,
                "position" integer DEFAULT NULL,
                "id_inscription" integer NOT NULL,
                FOREIGN KEY ("id_inscription") REFERENCES "Inscription"("id") ON DELETE CASCADE
            )');
            DB::statement('INSERT INTO "Resultat_new" SELECT * FROM "Resultat"');
            DB::statement('DROP TABLE "Resultat"');
            DB::statement('ALTER TABLE "Resultat_new" RENAME TO "Resultat"');
        } elseif ($driver === 'mysql') {
            Schema::table('Resultat', function (Blueprint $table) {
                $table->integer('position')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Pas de rollback — remettre NOT NULL casserait les données existantes
    }
};
