<?php

/**
 * @fileoverview PrixEvolutifSeeder.php
 * @description Crée les paliers de prix évolutifs pour les courses concernées.
 *              - 10km des Ponts : 2 paliers par dates (tarif augmente en août)
 *              - Urban Trail de Genève : 2 paliers par dossards (tarif augmente à 5500 inscrits)
 *              - Urban Trail 15km : 2 paliers par dossards
 *              Dépend de CourseSeeder.
 * @author Guillermet Jean-Daniel
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\PrixEvolutif;

class PrixEvolutifSeeder extends Seeder
{
    public function run(): void
    {
        // Prix évolutif par dates pour le 10km des Ponts
        $ponts10km = Course::where('nom', '10km des Ponts')->first();
        if ($ponts10km) {
            PrixEvolutif::updateOrCreate(
                ['id_course' => $ponts10km->id, 'ordre' => 1],
                ['type' => 'dates', 'valeur_debut' => '2026-06-01', 'valeur_fin' => '2026-07-31', 'tarif' => 25.00]
            );
            PrixEvolutif::updateOrCreate(
                ['id_course' => $ponts10km->id, 'ordre' => 2],
                ['type' => 'dates', 'valeur_debut' => '2026-08-01', 'valeur_fin' => '2026-09-05', 'tarif' => 35.00]
            );
        }

        // Prix évolutif par dossards pour l'Urban Trail 30km
        $trail = Course::where('nom', 'Urban Trail de Genève')->first();
        if ($trail) {
            PrixEvolutif::updateOrCreate(
                ['id_course' => $trail->id, 'ordre' => 1],
                ['type' => 'dossards', 'valeur_debut' => '5000', 'valeur_fin' => '5500', 'tarif' => 55.00]
            );
            PrixEvolutif::updateOrCreate(
                ['id_course' => $trail->id, 'ordre' => 2],
                ['type' => 'dossards', 'valeur_debut' => '5501', 'valeur_fin' => '6000', 'tarif' => 75.00]
            );
        }

        // Prix évolutif par dossards pour l'Urban Trail 15km
        $trail15 = Course::where('nom', 'Urban Trail 15km')->first();
        if ($trail15) {
            PrixEvolutif::updateOrCreate(
                ['id_course' => $trail15->id, 'ordre' => 1],
                ['type' => 'dossards', 'valeur_debut' => '6001', 'valeur_fin' => '7000', 'tarif' => 35.00]
            );
            PrixEvolutif::updateOrCreate(
                ['id_course' => $trail15->id, 'ordre' => 2],
                ['type' => 'dossards', 'valeur_debut' => '7001', 'valeur_fin' => '7500', 'tarif' => 50.00]
            );
        }
    }
}
