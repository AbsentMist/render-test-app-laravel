<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL : modification de l'ENUM pour ajouter 'Échangé'
            DB::statement("
                ALTER TABLE `Inscription`
                MODIFY COLUMN `status_paiement`
                ENUM('Validé','En attente','Annulé','Échangé')
                NOT NULL DEFAULT 'En attente'
            ");
        }
        // SQLite ne supporte pas ENUM ni MODIFY COLUMN.
        // Le champ est déjà stocké comme TEXT en SQLite,
        // donc aucune modification nécessaire — la valeur 'Échangé'
        // sera acceptée nativement. La validation est gérée côté Laravel.
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // /!\ Les lignes avec status_paiement = 'Échangé' passeront en valeur vide — à gérer avant rollback
            DB::statement("
                ALTER TABLE `Inscription`
                MODIFY COLUMN `status_paiement`
                ENUM('Validé','En attente','Annulé')
                NOT NULL DEFAULT 'En attente'
            ");
        }
    }
};
