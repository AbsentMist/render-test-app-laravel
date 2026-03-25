<?php

namespace Database\Seeders;

use App\Models\Evenement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvenementSeeder extends Seeder
{
    public function run(): void
    {
        $evenements = [
            [
                'nom'               => 'Course des Ponts 2026',
                'site'              => 'https://coursedesponts.ch',
                'couleur_primaire'  => '#0e0f54',
                'couleur_secondaire'=> '#d9f20b',
                'is_actif'          => 1,
                'is_rabais'         => 0,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Antigel Run 2026',
                'site'              => 'https://antigelrun.ch',
                'couleur_primaire'  => '#1a1a2e',
                'couleur_secondaire'=> '#e94560',
                'is_actif'          => 1,
                'is_rabais'         => 1,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Geneva Marathon 2026',
                'site'              => 'https://genevamarathon.com',
                'couleur_primaire'  => '#003366',
                'couleur_secondaire'=> '#ff6600',
                'is_actif'          => 1,
                'is_rabais'         => 1,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Trail du Grand Genève 2026',
                'site'              => 'https://trailsaleve.ch',
                'couleur_primaire'  => '#2d5a27',
                'couleur_secondaire'=> '#f5c518',
                'is_actif'          => 0,
                'is_rabais'         => 0,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Course Interne RHE 2026',
                'site'              => 'esefes',
                'couleur_primaire'  => '#4a4a4a',
                'couleur_secondaire'=> '#ffffff',
                'is_actif'          => 1,
                'is_rabais'         => 0,
                'is_interne'        => 1,
                'logo'              => null,
            ],
            // Événement de test complet — toutes les étapes activées
            [
                'nom'               => 'Nocturne des Evaux',
                'site'              => 'https://nocturnedesevaux.ch',
                'couleur_primaire'  => '#6b6b9e',
                'couleur_secondaire'=> '#ffffff',
                'is_actif'          => 1,
                'is_rabais'         => 1,
                'is_interne'        => 0,
                'logo'              => null,
            ],
        ];

        foreach ($evenements as $evenementData) {
            Evenement::updateOrCreate(
                ['nom' => $evenementData['nom']],
                $evenementData
            );
        }
    }
}