<?php

/**
 * @fileoverview OptionSeeder.php
 * @description Crée les options disponibles lors de l'inscription et les lie aux courses.
 *              - Options quantifiables : repas pasta (bolognaise, pesto)
 *              - Options cochables : navette transport
 *              Les options pasta et navette sont liées aux 4 principales courses.
 *              Dépend de CourseSeeder.
 * @author Neris Alessandro
 * @author Guillermet Jean-Daniel
 * @author Ngoie Steven
 */

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
        // =====================================================
        // OPTIONS QUANTIFIABLES (Repas)
        // =====================================================
        $optionsQuantifiables = [
            [
                'nom'         => '1 Entrée + 1 pasta bolognaise',
                'description' => 'Réservation entrée + pasta non-participant — CHF 19.00 / paiement à RUNNINGENEVA ASSOCIATION',
                'tarif'       => 15.00,
                'qte_min'     => 1,
                'qte_max'     => 10,
            ],
            [
                'nom'         => '1 Entrée + 1 pasta pesto',
                'description' => 'Réservation entrée + pasta non-participant — CHF 19.00 / paiement à RUNNINGENEVA ASSOCIATION',
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
                ['quantiteMin' => $data['qte_min'], 'quantiteMax' => $data['qte_max']]
            );
        }

        // =====================================================
        // OPTIONS COCHABLES
        // =====================================================
        $navette = Option::updateOrCreate(
            ['nom' => "J'utilise les navettes transport de l'organisation"],
            [
                'description' => "Transport organisé par l'événement — aller/retour",
                'tarif'       => 2.00,
                'type'        => 'Cochable',
                'modele'      => true,
            ]
        );
        OptionCochable::updateOrCreate(['id' => $navette->id], ['is_coche' => false]);

        // =====================================================
        // LIAISONS OPTIONS → COURSES
        // =====================================================
        $pasta = Option::where('nom', '1 Entrée + 1 pasta bolognaise')->first();

        $coursesAvecPasta = Course::whereIn('nom', [
            '10km des Ponts',
            'Antigel Night Run 10km',
            'Urban Trail de Genève',
            'Nocturne Relais 2x5km',
        ])->get();

        foreach ($coursesAvecPasta as $course) {
            DB::table('OptionPourCourse')->updateOrInsert(
                ['id_course' => $course->id, 'id_option' => $pasta->id]
            );
            DB::table('OptionPourCourse')->updateOrInsert(
                ['id_course' => $course->id, 'id_option' => $navette->id]
            );
        }
    }
}
