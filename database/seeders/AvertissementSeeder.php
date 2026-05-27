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
                'titre'   => 'Charte participant geneva urban trail',
                'contenu' => "CHARTE DU PARTICIPANT DU GENEVA URBAN TRAIL
                                Tout participant aux courses Geneva Urban trail s'engage de facto à la confirmation de son inscription à respecter les règles de circulation routières en vigueur (la LCR) sur les parcours.  
                                Le participant s'engage à courir sur les trottoirs, traverser les routes sur les passages piétons ou sur les endroits indiqués par les commissaires de course ou par la signalétique de la course (signalétique de couleur jaune)
                                Dans le même temps le participant s'engage à respecter son environnement naturel. Il ne jettera aucun déchet durant sa course sur les parcours. Il attendra son arrivée sur le village course pour bénéficier des poubelles de tris mis à sa disposition.
                                Le comité vous remercie de respecter la charte du participant au Geneva Urban Trail et vous souhaite une excellente course trail.",
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
                'titre'   => 'Charte participant trail du grand geneve',
                'contenu' => "CHARTE DU PARTICIPANT DU TRAI DU GRAND GENÈVE
                                Tout participant aux courses du Trail du GRAND GENÈVE s'engage de facto à la confirmation de son inscription à respecter les règles de circulation routières en vigueur (la LCR) sur les parcours.  
                                Le participant s'engage à courir sur les trottoirs, traverser les routes sur les passages piétons ou sur les endroits indiqués par les commissaires de course ou par la signalétique de la course (signalétique de couleur jaune)
                                Dans le même temps le participant s'engage à respecter son environnement naturel. Il ne jettera aucun déchet durant sa course sur les parcours. Il attendra son arrivée sur le village course pour bénéficier des poubelles de tris mis à sa disposition.
                                Le comité vous remercie de respecter étroitement cette charte et vous souhaite une excellente course trail.",
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
