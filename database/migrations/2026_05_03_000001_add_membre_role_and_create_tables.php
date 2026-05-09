<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modifie l'enum Role pour ajouter 'Membre'
        // Pour MariaDB/MySQL: ALTER TABLE
        // Pour SQLite: gérer la modification d'enum (texte avec CHECK)
        if (DB::getDriverName() === 'mysql') {
            // MariaDB/MySQL - modification de l'ENUM
            DB::statement("ALTER TABLE `Role` CHANGE `type` `type` ENUM('Participant', 'Administrateur', 'Membre') UNIQUE");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite - recréer la table avec la nouvelle contrainte CHECK
            // Étape 1: Désactiver les contraintes de clés étrangères
            DB::statement('PRAGMA foreign_keys = OFF');
            
            try {
                // Étape 2: Créer une table temporaire avec la nouvelle structure
                DB::statement("
                    CREATE TABLE Role_new (
                        id INTEGER PRIMARY KEY,
                        type TEXT UNIQUE NOT NULL CHECK (type IN ('Participant', 'Administrateur', 'Membre'))
                    )
                ");
                
                // Étape 3: Copier les données
                DB::statement("INSERT INTO Role_new (id, type) SELECT id, type FROM Role");
                
                // Étape 4: Supprimer l'ancienne table
                DB::statement("DROP TABLE Role");
                
                // Étape 5: Renommer la nouvelle table
                DB::statement("ALTER TABLE Role_new RENAME TO Role");
                
            } finally {
                // Réactiver les contraintes
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        // Création de la table Membre
        Schema::create('Membre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->unique()->constrained('User')->onDelete('cascade');
            $table->timestamps();
        });

        // Création de la table DemandeMembership
        Schema::create('DemandeMembership', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 255)->unique();
            $table->string('adresse', 255);
            $table->string('code_postal', 10);
            $table->string('ville', 100);
            $table->string('pays', 100);
            $table->string('telephone', 20);
            $table->date('date_naissance');
            $table->text('description');
            $table->enum('status', ['En attente', 'Approuvée', 'Refusée'])->default('En attente');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_decision')->nullable();
            $table->foreignId('id_admin_decideur')->nullable()->constrained('User')->onDelete('set null');
            $table->text('notes_admin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DemandeMembership');
        Schema::dropIfExists('Membre');

        // Revenir à l'enum original
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `Role` CHANGE `type` `type` ENUM('Participant', 'Administrateur') UNIQUE");
        } elseif (DB::getDriverName() === 'sqlite') {
            // Recréer avec l'ancien enum
            DB::statement('PRAGMA foreign_keys = OFF');
            
            try {
                DB::statement("
                    CREATE TABLE Role_new (
                        id INTEGER PRIMARY KEY,
                        type TEXT UNIQUE NOT NULL CHECK (type IN ('Participant', 'Administrateur'))
                    )
                ");
                
                DB::statement("INSERT INTO Role_new (id, type) SELECT id, type FROM Role WHERE type IN ('Participant', 'Administrateur')");
                
                DB::statement("DROP TABLE Role");
                
                DB::statement("ALTER TABLE Role_new RENAME TO Role");
                
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }
    }
};
