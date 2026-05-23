<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CodeDossard;
use App\Models\Course;

class CodeDossardSeeder extends Seeder
{
    public function run(): void
    {
        $ponts10km = Course::where('nom', '10km des Ponts')->first();
        $antigel   = Course::where('nom', 'Antigel Night Run 10km')->first();

        $codes = [
            [
                'code'                   => 'MRBEAST',
                'nom_personnalise'        => 'MrBeast',
                'id_course'              => $ponts10km?->id,
                'utilisations_max'       => 1,
                'utilisations_actuelles' => 0,
            ],
            [
                'code'                   => 'SUPERRUNNER',
                'nom_personnalise'        => 'SuperRunner',
                'id_course'              => $ponts10km?->id,
                'utilisations_max'       => 1,
                'utilisations_actuelles' => 0,
            ],
            [
                'code'                   => 'TEAMRGVA',
                'nom_personnalise'        => 'Team RGVA',
                'id_course'              => $antigel?->id,
                'utilisations_max'       => 5,
                'utilisations_actuelles' => 0,
            ],
        ];

        foreach ($codes as $data) {
            if (!$data['id_course']) continue;
            CodeDossard::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
