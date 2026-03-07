<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Option;
use App\Models\OptionQuantifiable;
use App\Models\OptionCochable;
use App\Models\Course;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Options de type 'Quantifiable' (Pasta)
        $optionsQuantifiables = [
            [
                'nom'         => '1 Entrée + 1 pasta bolognaise',
                'description' => 'Réservation entrée + pasta non-participant CHF 19,00 / paiement à RUNNINGENEVA ASSOCIATION',
                'tarif'       => 15.00,
                'qte_min'     => 1,
                'qte_max'     => 10,
            ],
            [
                'nom'         => '1 Entrée + 1 pasta pesto',
                'description' => 'Réservation entrée + pasta non-participant CHF 19,00 / paiement à RUNNINGENEVA ASSOCIATION',
                'tarif'       => 15.00,
                'qte_min'     => 1,
                'qte_max'     => 10,
            ],
        ];

        foreach ($optionsQuantifiables as $data) {
            $option = Option::updateOrCreate(
                ['nom' => $data['nom']],
                [
                    'description' => $data['description'],
                    'tarif'       => $data['tarif'],
                    'type'        => 'Quantifiable',
                    'modele'      => true,
                ]
            );
            OptionQuantifiable::updateOrCreate(
                ['id' => $option->id],
                [
                    'quantiteMin' => $data['qte_min'],
                    'quantiteMax' => $data['qte_max'],
                ]
            );
        }

        // 2. Option de type 'Cochable' (Navettes)
        $navette = Option::updateOrCreate(
            ['nom' => "J'utilise les navettes transports de l'organisation"],
            [
                'description' => "Transport organisé par l'événement",
                'tarif'       => 2.00,
                'type'        => 'Cochable',
                'modele'      => true,
            ]
        );
        OptionCochable::updateOrCreate(
            ['id' => $navette->id],
            ['is_coche' => false]
        );

        // 3. Liaisons options → courses
        $pasta = Option::where('nom', '1 Entrée + 1 pasta bolognaise')->first();

        $coursesAvecOptions = Course::whereIn('nom', [
            'Nocturne des Evaux - Challenge',
            '10km des Ponts',
            'Antigel Night Run',
            'Marathon de Genève',
        ])->get();

        foreach ($coursesAvecOptions as $course) {
            // updateOrCreate pour éviter les doublons
            DB::table('OptionPourCourse')->updateOrInsert(
                ['id_course' => $course->id, 'id_option' => $pasta->id]
            );
            DB::table('OptionPourCourse')->updateOrInsert(
                ['id_course' => $course->id, 'id_option' => $navette->id]
            );
        }
    }
}