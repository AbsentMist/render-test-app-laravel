<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Désactivation de la vérification des clés étrangères pendant la création
        Schema::disableForeignKeyConstraints();

        //Tables sans contraintes de clés étrangères
        Schema::create('Role', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Participant', 'Administrateur'])->unique();
        });

        Schema::create('Evenement', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 180);
            $table->binary('logo');
            $table->string('site', 255);
            $table->string('couleur_primaire', 10);
            $table->string('couleur_secondaire', 10);
            $table->boolean('is_avertissement')->default(0);
            $table->boolean('is_document')->default(0);
            $table->boolean('is_questionnaire')->default(0);
            $table->boolean('is_rabais')->default(0);
            $table->boolean('is_actif')->default(0);
            $table->boolean('is_interne')->default(0);
        });

        Schema::create('Categorie', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
        });

        Schema::create('SousCategorie', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
        });

        Schema::create('Groupe', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('code_entreprise', 100)->nullable();
            $table->string('type', 50); 
        });

        Schema::create('Template', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 255);
        });

        Schema::create('Champ', function (Blueprint $table) {
            $table->id();
            $table->string('texte', 255);
        });

        Schema::create('Question', function (Blueprint $table) {
            $table->id();
            $table->string('enonce', 255);
        });

        Schema::create('Option', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->string('description', 255)->nullable();
            $table->binary('image')->nullable();
        });

        //Tables avec contraintes de clés étrangères
        Schema::create('User', function (Blueprint $table) {
            $table->id();
            $table->string('email', 80)->unique();
            $table->string('password', 100);
        });

        Schema::create('Avertissement', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->text('contenu');
            $table->foreignId('id_evenement')->constrained('Evenement')->onDelete('cascade');
        });

        Schema::create('Course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_evenement')->constrained('Evenement')->onDelete('cascade');
            $table->foreignId('id_categorie')->nullable()->constrained('Categorie')->onDelete('set null'); //Enlever nullable une fois les catégories créées
            $table->foreignId('id_sous_categorie')->nullable()->constrained('SousCategorie')->onDelete('set null');
            $table->string('nom', 120);
            $table->date('date');
            $table->date('debut_inscription');
            $table->date('fin_inscription');
            $table->float('tarif')->default(0);
            $table->string('status', 50);
            $table->string('type', 50);
            $table->boolean('challenge')->default(0);
            $table->boolean('is_actif')->default(1);
            $table->integer('max_inscription');
            $table->integer('premier_dossard');
            $table->integer('dernier_dossard');
            $table->float('distance')->nullable();
            $table->time('heure_depart')->nullable();
            $table->time('heure_fin')->nullable();
            $table->integer('age_minimum');
            $table->integer('age_maximum')->nullable();
        });

        //Table d'association
        Schema::create('EvenementQuestion', function (Blueprint $table) {
            $table->foreignId('id_evenement')->constrained('Evenement')->onDelete('cascade');
            $table->foreignId('id_question')->constrained('Question')->onDelete('cascade');
            $table->primary(['id_evenement', 'id_question']);
        });

        Schema::create('OptionQuestion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_question')->constrained('Question')->onDelete('cascade');
            $table->string('texte_option', 255);
        });

        Schema::create('TemplatePersonnalise', function (Blueprint $table) {
            $table->foreignId('id_template')->constrained('Template')->onDelete('cascade');
            $table->foreignId('id_champ')->constrained('Champ')->onDelete('cascade');
            $table->string('nom', 255);
            $table->primary(['id_template', 'id_champ']);
        });

        //Table d'association
        Schema::create('UserRole', function (Blueprint $table) {
            $table->foreignId('id_user')->constrained('User')->onDelete('cascade');
            $table->foreignId('id_role')->constrained('Role')->onDelete('cascade');
            $table->primary(['id_user', 'id_role']);
        });

        Schema::create('Administrateur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('User')->onDelete('cascade');
        });

        Schema::create('Participant', function (Blueprint $table) { //Attention : doublons avec autre fichier migration
            $table->id();
            $table->foreignId('id_user')->constrained('User')->onDelete('cascade');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->date('date_naissance')->nullable();
            $table->string('equipe_nom', 100)->nullable();
            $table->string('adresse', 100)->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('pays', 100)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('nationalite', 100)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('taille_tshirt', 10)->nullable();
            $table->string('sexe', 10)->nullable();
            $table->binary('photo')->nullable();
            $table->binary('banniere')->nullable();
        });

        Schema::create('Document', function (Blueprint $table) {
            $table->id();
            $table->string('url', 255);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('valable')->default(0);
            $table->foreignId('id_participant')->constrained('Participant')->onDelete('cascade');
        });

        Schema::create('OptionPourCourse', function (Blueprint $table) {
            $table->foreignId('id_option')->constrained('Option')->onDelete('cascade');
            $table->foreignId('id_course')->constrained('Course')->onDelete('cascade');
            $table->primary(['id_option', 'id_course']);
        });

        // Héritages d'Option
        Schema::create('OptionQuantifiable', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreign('id')->references('id')->on('Option')->onDelete('cascade');
            $table->integer('nombre')->default(0);
        });

        Schema::create('OptionCochable', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreign('id')->references('id')->on('Option')->onDelete('cascade');
            $table->boolean('is_coche')->default(0);
        });

        //Table d'association
        Schema::create('Inscription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_participant')->constrained('Participant')->onDelete('cascade');
            $table->foreignId('id_course')->constrained('Course')->onDelete('cascade');
            $table->foreignId('id_groupe')->nullable()->constrained('Groupe')->onDelete('set null');
            $table->foreignId('id_document')->nullable()->constrained('Document')->onDelete('set null');
            
            $table->string('code_participant', 100)->nullable();
            $table->float('tarif')->default(0);
            $table->string('status_paiement', 50)->nullable();
            $table->float('montant_rabais')->default(0);
            $table->boolean('avertissement_valide')->default(0);
        });

        Schema::create('Dossard', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');
            $table->string('nom_personnalise', 150)->nullable();
            $table->boolean('retrait_dossard')->default(0);
            $table->foreignId('id_inscription')->constrained('Inscription')->onDelete('cascade');
        });

        Schema::create('Resultat', function (Blueprint $table) {
            $table->id();
            $table->time('temps_course')->nullable();
            $table->integer('position')->nullable();
            $table->foreignId('id_inscription')->constrained('Inscription')->onDelete('cascade');
        });

        //Table d'association
        Schema::create('ReponseQuestion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_question')->constrained('Question')->onDelete('cascade');
            $table->foreignId('id_option_choisie')->nullable()->constrained('OptionQuestion')->onDelete('set null');
            $table->foreignId('id_inscription')->constrained('Inscription')->onDelete('cascade');
        });

        //Table d'association
        Schema::create('GroupeParticipant', function (Blueprint $table) {
            $table->foreignId('id_groupe')->constrained('Groupe')->onDelete('cascade');
            $table->foreignId('id_participant')->constrained('Participant')->onDelete('cascade');
            $table->string('statut', 50)->nullable();
            $table->primary(['id_groupe', 'id_participant']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // En cas de supression, désactivation des contraintes pour supprimer les tables dans n'importe quel ordre.
        Schema::disableForeignKeyConstraints();

        $tables = [
            'GroupeParticipant', 'ReponseQuestion', 'Resultat', 'Dossard', 'Inscription',
            'OptionCochable', 'OptionQuantifiable', 'OptionPourCourse', 'Document', 
            'Participant', 'Administrateur', 'UserRole', 'TemplatePersonnalise', 
            'OptionQuestion', 'EvenementQuestion', 'Course', 'Avertissement', 'User',
            'Option', 'Question', 'Champ', 'Template', 'Groupe', 'SousCategorie', 
            'Categorie', 'Evenement', 'Role'
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};