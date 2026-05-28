<?php

/**
 * @fileoverview EvenementSeeder.php
 * @description Crée les événements sportifs de test avec leurs logos (chargés depuis public/images/).
 *              6 événements couvrant les cas principaux :
 *              - 2 épinglés (ordre 1 et 2) mis en avant sur la page d'accueil
 *              - 2 standards actifs
 *              - 1 interne (réservé aux membres)
 *              - 1 inactif (à venir, non visible par les participants)
 *              Les logos sont lus depuis le disque au moment du seed — absents = null sans erreur.
 * @author Neris Alessandro
 * @author Guillermet Jean-Daniel
 * @author Ngoie Steven
 */

namespace Database\Seeders;

use App\Models\Evenement;
use Illuminate\Database\Seeder;

class EvenementSeeder extends Seeder
{
    public function run(): void
    {
        $publicPath = public_path('images');
        
        $evenements = [
            // Épinglé #1 — événement principal mis en avant
            [
                'nom'                => 'Course des Ponts 2026',
                'site'               => 'https://course-des-ponts.ch/',
                'couleur_primaire'   => '#0e0f54',
                'couleur_secondaire' => '#d9f20b',
                'is_actif'           => 1,
                'is_rabais'          => 1,
                'is_interne'         => 0,
                'logo'               => file_exists("$publicPath/Course_des_ponts.png") ? file_get_contents("$publicPath/Course_des_ponts.png") : null,
                'ordre'              => 1,
            ],
            // Épinglé #2
            [
                'nom'                => 'Geneva Urban Trail 2026',
                'site'               => 'https://geneva-urban-trail.ch/',
                'couleur_primaire'   => '#003366',
                'couleur_secondaire' => '#ff6600',
                'is_actif'           => 1,
                'is_rabais'          => 1,
                'is_interne'         => 0,
                'logo'               => file_exists("$publicPath/Geneva_urban_trail.png") ? file_get_contents("$publicPath/Geneva_urban_trail.png") : null,
                'ordre'              => 2,
            ],
            // Non épinglé — trié par date
            [
                'nom'                => 'Antigel Run 2026',
                'site'               => 'https://antigel.ch/event/antigel-run/',
                'couleur_primaire'   => '#1a1a2e',
                'couleur_secondaire' => '#e94560',
                'is_actif'           => 1,
                'is_rabais'          => 1,
                'is_interne'         => 0,
                'logo'               => file_exists("$publicPath/Antigel_run.png") ? file_get_contents("$publicPath/Antigel_run.png") : null,
                'ordre'              => null,
            ],
            // Événement complet — toutes les fonctionnalités
            [
                'nom'                => 'Nocturne des Evaux 2026',
                'site'               => 'https://nocturnedesevaux.ch',
                'couleur_primaire'   => '#6b6b9e',
                'couleur_secondaire' => '#ffffff',
                'is_actif'           => 1,
                'is_rabais'          => 1,
                'is_interne'         => 0,
                'logo'               => file_exists("$publicPath/Nocturne-des-evaux.png") ? file_get_contents("$publicPath/Nocturne-des-evaux.png") : null,
                'ordre'              => null,
            ],
            // Événement interne
            [
                'nom'                => 'Course Interne RHE 2026',
                'site'               => null,
                'couleur_primaire'   => '#4a4a4a',
                'couleur_secondaire' => '#ffffff',
                'is_actif'           => 1,
                'is_rabais'          => 0,
                'is_interne'         => 1,
                'logo'               => null,
                'ordre'              => null,
            ],
            // Événement inactif (à venir)
            [
                'nom'                => 'Trail du Grand Genève 2027',
                'site'               => 'https://traildugrandgeneve.com/',
                'couleur_primaire'   => '#2d5a27',
                'couleur_secondaire' => '#f5c518',
                'is_actif'           => 0,
                'is_rabais'          => 0,
                'is_interne'         => 0,
                'logo'               => file_exists("$publicPath/Trail_du_grand_geneve.png") ? file_get_contents("$publicPath/Trail_du_grand_geneve.png") : null,
                'ordre'              => null,
            ],
        ];

        foreach ($evenements as $data) {
            Evenement::updateOrCreate(['nom' => $data['nom']], $data);
        }
    }
}
