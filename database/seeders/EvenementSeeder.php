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
                'nom'               => 'Course des Ponts 2025',
                'site'              => 'https://coursedesponts.ch',
                'couleur_primaire'  => '#0e0f54',
                'couleur_secondaire'=> '#d9f20b',
                'is_actif'          => 1,
                'is_avertissement'  => 1,
                'is_document'       => 0,
                'is_questionnaire'  => 1,
                'is_rabais'         => 0,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Antigel Run 2025',
                'site'              => 'https://antigelrun.ch',
                'couleur_primaire'  => '#1a1a2e',
                'couleur_secondaire'=> '#e94560',
                'is_actif'          => 1,
                'is_avertissement'  => 1,
                'is_document'       => 0,
                'is_questionnaire'  => 0,
                'is_rabais'         => 1,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Geneva Marathon 2025',
                'site'              => 'https://genevamarathon.com',
                'couleur_primaire'  => '#003366',
                'couleur_secondaire'=> '#ff6600',
                'is_actif'          => 1,
                'is_avertissement'  => 0,
                'is_document'       => 1,
                'is_questionnaire'  => 1,
                'is_rabais'         => 1,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Trail du Grand Genève 2025',
                'site'              => 'https://trailsaleve.ch',
                'couleur_primaire'  => '#2d5a27',
                'couleur_secondaire'=> '#f5c518',
                'is_actif'          => 0,
                'is_avertissement'  => 1,
                'is_document'       => 1,
                'is_questionnaire'  => 0,
                'is_rabais'         => 0,
                'is_interne'        => 0,
                'logo'              => null,
            ],
            [
                'nom'               => 'Course Interne RHE 2025',
                'site'              => 'esefes',
                'couleur_primaire'  => '#4a4a4a',
                'couleur_secondaire'=> '#ffffff',
                'is_actif'          => 1,
                'is_avertissement'  => 0,
                'is_document'       => 0,
                'is_questionnaire'  => 0,
                'is_rabais'         => 0,
                'is_interne'        => 1,
                'logo'              => null,
            ],
        ];

        foreach ($evenements as $evenementData) {
            // On cherche par le nom (unique). S'il existe, on met à jour, sinon on crée.
            Evenement::updateOrCreate(
                ['nom' => $evenementData['nom']], 
                $evenementData
            );
        }
    }
}