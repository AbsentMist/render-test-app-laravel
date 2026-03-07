<?php

namespace Database\Seeders;

use App\Models\Avertissement;
use Illuminate\Database\Seeder;

class AvertissementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $avertissements = [
            [
                'titre'   => 'Course des ponts',
                'contenu' => 'Course urbaine avec de nombreux ponts à traverser, ce qui peut présenter un risque de chute en cas de pluie.',
                'modele'  => true,
            ],
            [
                'titre'   => 'Antigel',
                'contenu' => 'En raison de conditions météorologiques hivernales, du verglas peut être présent sur le parcours, ce qui peut rendre certaines sections glissantes.',
                'modele'  => true,
            ],
            [
                'titre'   => 'Trail Nocturne',
                'contenu' => 'Lampe frontale obligatoire. Le balisage est réfléchissant mais la visibilité reste limitée en forêt.',
                'modele'  => true,
            ],
            [
                'titre'   => 'Canicule',
                'contenu' => 'Risque de forte chaleur. Hydratation régulière fortement recommandée aux postes de ravitaillement.',
                'modele'  => true,
            ],
        ];

        foreach ($avertissements as $avertissementData) {
            // On cherche par le nom (unique). S'il existe, on met à jour, sinon on crée.
            Avertissement::updateOrCreate(
                ['titre' => $avertissementData['titre']], 
                $avertissementData
            );
        }
    }
}