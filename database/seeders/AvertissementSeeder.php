<?php

namespace Database\Seeders;

use App\Models\Avertissement;
use App\Models\Course;
use Illuminate\Database\Seeder;

class AvertissementSeeder extends Seeder
{
    public function run(): void
    {
        $avertissements = [
            [
                'titre'   => 'Course urbaine — risque de chute',
                'contenu' => 'Course urbaine avec de nombreux ponts à traverser. En cas de pluie, les surfaces peuvent être glissantes. Chaussures adaptées recommandées.',
                'modele'  => true,
                'courses' => ['10km des Ponts', '20km Groupe des Ponts'],
            ],
            [
                'titre'   => 'Course nocturne — visibilité réduite',
                'contenu' => 'Lampe frontale obligatoire. Le balisage est réfléchissant mais la visibilité reste limitée. Équipement fluorescent fortement recommandé.',
                'modele'  => true,
                'courses' => ['Antigel Night Run 10km', 'Nocturne Relais 2x5km'],
            ],
            [
                'titre'   => 'Conditions hivernales',
                'contenu' => 'En raison de conditions météorologiques hivernales, du verglas peut être présent sur le parcours. Des chaussures avec crampons sont recommandées.',
                'modele'  => true,
                'courses' => [],
            ],
            [
                'titre'   => 'Forte chaleur',
                'contenu' => 'Risque de forte chaleur. Hydratation régulière fortement recommandée aux postes de ravitaillement. Casquette et crème solaire conseillées.',
                'modele'  => true,
                'courses' => [],
            ],
        ];

        foreach ($avertissements as $data) {
            $avertissement = Avertissement::updateOrCreate(
                ['titre' => $data['titre']],
                [
                    'titre'   => $data['titre'],
                    'contenu' => $data['contenu'],
                    'modele'  => $data['modele'],
                ]
            );

            foreach ($data['courses'] as $nomCourse) {
                $course = Course::where('nom', $nomCourse)->first();
                if ($course) {
                    $course->update(['id_avertissement' => $avertissement->id]);
                }
            }
        }
    }
}
