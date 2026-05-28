<?php

/**
 * @fileoverview CodeRabaisSeeder.php
 * @description Crée les codes de réduction de test pour plusieurs courses.
 *              Inclut un code expiré (EXPIRE2025) pour tester les cas d'erreur
 *              et vérifier que les validations bloquent bien les codes invalides.
 *              Dépend de CourseSeeder.
 * @author Guillermet Jean-Daniel
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CodeRabais;
use App\Models\Course;

class CodeRabaisSeeder extends Seeder
{
    public function run(): void
    {
        $ponts10km = Course::where('nom', '10km des Ponts')->first();
        $antigel   = Course::where('nom', 'Antigel Night Run 10km')->first();
        $marathon  = Course::where('nom', 'Urban Trail de Genève')->first();

        $codes = [
            // Code % pour le 10km des Ponts
            [
                'code'                   => 'PONTS20',
                'type'                   => 'pourcentage',
                'valeur'                 => 20,
                'id_course'              => $ponts10km?->id,
                'utilisations_max'       => 50,
                'utilisations_actuelles' => 0,
                'date_expiration'        => '2026-09-05',
                'actif'                  => 1,
            ],
            // Code montant fixe pour l'Antigel
            [
                'code'                   => 'ANTIGEL10',
                'type'                   => 'montant_fixe',
                'valeur'                 => 10,
                'id_course'              => $antigel?->id,
                'utilisations_max'       => 30,
                'utilisations_actuelles' => 0,
                'date_expiration'        => '2026-11-07',
                'actif'                  => 1,
            ],
            // Code étudiant pour l'Urban Trail
            [
                'code'                   => 'ETUDIANT2026',
                'type'                   => 'pourcentage',
                'valeur'                 => 15,
                'id_course'              => $marathon?->id,
                'utilisations_max'       => null,
                'utilisations_actuelles' => 0,
                'date_expiration'        => '2026-09-27',
                'actif'                  => 1,
            ],
            // Code expiré (pour tester les cas d'erreur)
            [
                'code'                   => 'EXPIRE2025',
                'type'                   => 'montant_fixe',
                'valeur'                 => 5,
                'id_course'              => $ponts10km?->id,
                'utilisations_max'       => 10,
                'utilisations_actuelles' => 10,
                'date_expiration'        => '2025-12-31',
                'actif'                  => 0,
            ],
        ];

        foreach ($codes as $data) {
            if (!$data['id_course']) continue;
            CodeRabais::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
