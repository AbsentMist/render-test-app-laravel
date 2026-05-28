<?php

/**
 * @fileoverview CourseSeeder.php
 * @description Crée les courses de test rattachées aux événements créés par EvenementSeeder.
 *              Couvre tous les types et combinaisons de fonctionnalités pour les tests :
 *              - Course des Ponts 2026   : individuel + groupe, challenge, prix évolutif, questionnaire
 *              - Antigel Run 2026        : individuel + relais, document obligatoire, code dossard
 *              - Geneva Urban Trail 2026 : trail 30km + 15km, document, prix évolutif par dossards
 *              - Nocturne des Evaux 2026 : relais + individuel, toutes fonctionnalités
 *              - Course Interne RHE 2026 : course interne simple
 *              Dépend de EvenementSeeder.
 * @author Neris Alessandro
 * @author Guillermet Jean-Daniel
 * @author Ngoie Steven
 * 
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evenement;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $ponts    = Evenement::where('nom', 'Course des Ponts 2026')->first();
        $antigel  = Evenement::where('nom', 'Antigel Run 2026')->first();
        $marathon = Evenement::where('nom', 'Geneva Urban Trail 2026')->first();
        $nocturne = Evenement::where('nom', 'Nocturne des Evaux 2026')->first();
        $interne  = Evenement::where('nom', 'Course Interne RHE 2026')->first();

        $courses = [

            // =====================================================
            // COURSE DES PONTS 2026
            // Individuel + Challenge, avec avertissement, questionnaire, options, code rabais, prix évolutif
            // =====================================================
            [
                'id_evenement'        => $ponts->id,
                'nom'                 => '10km des Ponts',
                'date_debut'          => '2026-09-12',
                'date_fin'            => '2026-09-12',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-09-05',
                'tarif'               => 35.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => true,
                'is_dossard'          => false,
                'is_avertissement'    => true,
                'is_actif'            => true,
                'is_questionnaire'    => true,
                'is_prix_evolutif'    => true,
                'max_inscription'     => 500,
                'premier_dossard'     => 1,
                'dernier_dossard'     => 500,
                'distance'            => 10.0,
                'age_minimum'         => 16,
                'age_maximum'         => 99,
                'max_nb_personne'     => null,
                'document_description'=> null,
            ],
            [
                'id_evenement'        => $ponts->id,
                'nom'                 => '5km Populaire',
                'date_debut'          => '2026-09-12',
                'date_fin'            => '2026-09-12',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-09-05',
                'tarif'               => 20.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => false,
                'is_dossard'          => false,
                'is_avertissement'    => false,
                'is_actif'            => true,
                'is_questionnaire'    => false,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 300,
                'premier_dossard'     => 1001,
                'dernier_dossard'     => 1300,
                'distance'            => 5.0,
                'age_minimum'         => 12,
                'age_maximum'         => 99,
                'max_nb_personne'     => null,
                'document_description'=> null,
            ],
            [
                'id_evenement'        => $ponts->id,
                'nom'                 => '20km Groupe des Ponts',
                'date_debut'          => '2026-09-12',
                'date_fin'            => '2026-09-12',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-09-05',
                'tarif'               => 30.00,
                'status'              => 'Ouvert',
                'type'                => 'Groupe',
                'is_challenge'        => true,
                'is_dossard'          => true,
                'is_avertissement'    => true,
                'is_actif'            => true,
                'is_questionnaire'    => false,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 200,
                'premier_dossard'     => 2001,
                'dernier_dossard'     => 2200,
                'distance'            => 20.0,
                'age_minimum'         => 18,
                'age_maximum'         => null,
                'max_nb_personne'     => 4,
                'document_description'=> null,
            ],

            // =====================================================
            // ANTIGEL RUN 2026
            // Individuel, avec document obligatoire, code dossard personnalisé
            // =====================================================
            [
                'id_evenement'        => $antigel->id,
                'nom'                 => 'Antigel Night Run 10km',
                'date_debut'          => '2026-11-14',
                'date_fin'            => '2026-11-14',
                'debut_inscription'   => '2026-09-01',
                'fin_inscription'     => '2026-11-07',
                'tarif'               => 40.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => true,
                'is_dossard'          => false,
                'is_avertissement'    => true,
                'is_actif'            => true,
                'is_questionnaire'    => true,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 1000,
                'premier_dossard'     => 2000,
                'dernier_dossard'     => 3000,
                'distance'            => 10.0,
                'age_minimum'         => 18,
                'age_maximum'         => null,
                'max_nb_personne'     => null,
                'document_description'=> 'Certificat médical de moins de 6 mois attestant l\'aptitude à la course à pied',
            ],
            [
                'id_evenement'        => $antigel->id,
                'nom'                 => 'Antigel Relais 4x2km',
                'date_debut'          => '2026-11-14',
                'date_fin'            => '2026-11-14',
                'debut_inscription'   => '2026-09-01',
                'fin_inscription'     => '2026-11-07',
                'tarif'               => 25.00,
                'status'              => 'Ouvert',
                'type'                => 'Relais',
                'is_challenge'        => false,
                'is_dossard'          => false,
                'is_avertissement'    => false,
                'is_actif'            => true,
                'is_questionnaire'    => false,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 200,
                'premier_dossard'     => 3001,
                'dernier_dossard'     => 3200,
                'distance'            => 2.0,
                'age_minimum'         => 16,
                'age_maximum'         => null,
                'max_nb_personne'     => 4,
                'document_description'=> null,
            ],

            // =====================================================
            // GENEVA URBAN TRAIL 2026
            // Trail individuel 30km + 15km, avec document, prix évolutif
            // =====================================================
            [
                'id_evenement'        => $marathon->id,
                'nom'                 => 'Urban Trail de Genève',
                'date_debut'          => '2026-10-04',
                'date_fin'            => '2026-10-04',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-09-27',
                'tarif'               => 75.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => true,
                'is_dossard'          => false,
                'is_avertissement'    => false,
                'is_actif'            => true,
                'is_questionnaire'    => true,
                'is_prix_evolutif'    => true,
                'max_inscription'     => 1000,
                'premier_dossard'     => 5000,
                'dernier_dossard'     => 6000,
                'distance'            => 30.0,
                'age_minimum'         => 18,
                'age_maximum'         => null,
                'max_nb_personne'     => null,
                'document_description'=> 'Certificat médical de moins de 12 mois (obligatoire pour le trail)',
            ],
            [
                'id_evenement'        => $marathon->id,
                'nom'                 => 'Urban Trail 15km',
                'date_debut'          => '2026-10-04',
                'date_fin'            => '2026-10-04',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-09-27',
                'tarif'               => 50.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => true,
                'is_dossard'          => false,
                'is_avertissement'    => false,
                'is_actif'            => true,
                'is_questionnaire'    => false,
                'is_prix_evolutif'    => true,
                'max_inscription'     => 1500,
                'premier_dossard'     => 6001,
                'dernier_dossard'     => 7500,
                'distance'            => 15.0,
                'age_minimum'         => 16,
                'age_maximum'         => null,
                'max_nb_personne'     => null,
                'document_description'=> null,
            ],

            // =====================================================
            // NOCTURNE DES EVAUX 2026
            // Toutes les fonctionnalités : relais + challenge + options + questionnaire + avertissement
            // =====================================================
            [
                'id_evenement'        => $nocturne->id,
                'nom'                 => 'Nocturne Relais 2x5km',
                'date_debut'          => '2026-08-22',
                'date_fin'            => '2026-08-22',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-08-15',
                'tarif'               => 45.00,
                'status'              => 'Ouvert',
                'type'                => 'Relais',
                'is_challenge'        => true,
                'is_dossard'          => false,
                'is_avertissement'    => true,
                'is_actif'            => true,
                'is_questionnaire'    => true,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 100,
                'premier_dossard'     => 1,
                'dernier_dossard'     => 100,
                'distance'            => 5.0,
                'age_minimum'         => 16,
                'age_maximum'         => null,
                'max_nb_personne'     => 2,
                'document_description'=> null,
            ],
            [
                'id_evenement'        => $nocturne->id,
                'nom'                 => 'Nocturne 5km Individuel',
                'date_debut'          => '2026-08-22',
                'date_fin'            => '2026-08-22',
                'debut_inscription'   => '2026-06-01',
                'fin_inscription'     => '2026-08-15',
                'tarif'               => 20.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => false,
                'is_dossard'          => false,
                'is_avertissement'    => false,
                'is_actif'            => true,
                'is_questionnaire'    => false,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 200,
                'premier_dossard'     => 101,
                'dernier_dossard'     => 300,
                'distance'            => 5.0,
                'age_minimum'         => 12,
                'age_maximum'         => null,
                'max_nb_personne'     => null,
                'document_description'=> null,
            ],

            // =====================================================
            // COURSE INTERNE RHE 2026
            // Interne, individuel simple
            // =====================================================
            [
                'id_evenement'        => $interne->id,
                'nom'                 => 'Course RHE 10km',
                'date_debut'          => '2026-07-04',
                'date_fin'            => '2026-07-04',
                'debut_inscription'   => '2026-05-01',
                'fin_inscription'     => '2026-06-28',
                'tarif'               => 15.00,
                'status'              => 'Ouvert',
                'type'                => 'Individuel',
                'is_challenge'        => false,
                'is_dossard'          => true,
                'is_avertissement'    => false,
                'is_actif'            => true,
                'is_questionnaire'    => false,
                'is_prix_evolutif'    => false,
                'max_inscription'     => 50,
                'premier_dossard'     => 1,
                'dernier_dossard'     => 50,
                'distance'            => 10.0,
                'age_minimum'         => 18,
                'age_maximum'         => null,
                'max_nb_personne'     => null,
                'document_description'=> null,
            ],
        ];

        foreach ($courses as $data) {
            Course::updateOrCreate(['nom' => $data['nom']], $data);
        }
    }
}