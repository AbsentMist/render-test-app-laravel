<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Evenement;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $ponts    = Evenement::where('nom', 'Course des Ponts 2025')->first();
        $antigel  = Evenement::where('nom', 'Antigel Run 2025')->first();
        $marathon = Evenement::where('nom', 'Geneva Marathon 2025')->first();
        $nocturne = Evenement::where('nom', 'Nocturne des Evaux')->first();

        $courses = [
            // ===== Courses existantes (inchangées) =====
            [
                'id_evenement'      => $ponts->id,
                'nom'               => '10km des Ponts',
                'date_debut'        => '2025-05-15',
                'date_fin'          => '2025-05-15',
                'debut_inscription' => '2025-01-01',
                'fin_inscription'   => '2025-05-10',
                'tarif'             => 35.00,
                'status'            => 'Ouvert',
                'type'              => 'Route',
                'is_challenge'      => true,
                'is_dossard'        => true,
                'is_avertissement'  => false,
                'is_actif'          => true,
                'max_inscription'   => 500,
                'premier_dossard'   => 1,
                'dernier_dossard'   => 500,
                'distance'          => 10.0,
                'heure_depart'      => '09:00:00',
                'heure_fin'         => '11:00:00',
                'age_minimum'       => 16,
                'age_maximum'       => 99,
            ],
            [
                'id_evenement'      => $ponts->id,
                'nom'               => '5km Populaire',
                'date_debut'        => '2025-05-15',
                'date_fin'          => '2025-05-15',
                'debut_inscription' => '2025-01-01',
                'fin_inscription'   => '2025-05-10',
                'tarif'             => 20.00,
                'status'            => 'Ouvert',
                'type'              => 'Route',
                'is_challenge'      => false,
                'is_dossard'        => false,
                'is_avertissement'  => false,
                'is_actif'          => true,
                'max_inscription'   => 300,
                'premier_dossard'   => 1001,
                'dernier_dossard'   => 1300,
                'distance'          => 5.0,
                'heure_depart'      => '10:30:00',
                'heure_fin'         => '12:00:00',
                'age_minimum'       => 12,
                'age_maximum'       => 99,
            ],
            [
                'id_evenement'      => $antigel->id,
                'nom'               => 'Antigel Night Run',
                'date_debut'        => '2025-01-20',
                'date_fin'          => '2025-01-20',
                'debut_inscription' => '2024-11-01',
                'fin_inscription'   => '2025-01-15',
                'tarif'             => 40.00,
                'status'            => 'Ouvert',
                'type'              => 'Trail Urbain',
                'is_challenge'      => true,
                'is_dossard'        => true,
                'is_avertissement'  => false,
                'is_actif'          => true,
                'max_inscription'   => 1000,
                'premier_dossard'   => 2000,
                'dernier_dossard'   => 3000,
                'distance'          => 7.5,
                'heure_depart'      => '18:30:00',
                'heure_fin'         => '21:00:00',
                'age_minimum'       => 18,
                'age_maximum'       => null,
            ],
            [
                'id_evenement'      => $marathon->id,
                'nom'               => 'Marathon de Genève',
                'date_debut'        => '2025-05-04',
                'date_fin'          => '2025-05-04',
                'debut_inscription' => '2024-09-01',
                'fin_inscription'   => '2025-04-25',
                'tarif'             => 95.00,
                'status'            => 'Ouvert',
                'type'              => 'Marathon',
                'is_challenge'      => true,
                'is_dossard'        => true,
                'is_avertissement'  => false,
                'is_actif'          => true,
                'max_inscription'   => 2500,
                'premier_dossard'   => 5000,
                'dernier_dossard'   => 7500,
                'distance'          => 42.195,
                'heure_depart'      => '08:00:00',
                'heure_fin'         => '14:00:00',
                'age_minimum'       => 18,
                'age_maximum'       => null,
            ],
            // ===== Nocturne des Evaux — ajout JD pour tests inscription =====
            [
                'id_evenement'      => $nocturne->id,
                'nom'               => 'Nocturne des Evaux - Challenge',
                'date_debut'        => '2026-04-10',
                'date_fin'          => '2026-04-10',
                'debut_inscription' => '2026-01-01',
                'fin_inscription'   => '2026-04-05',
                'tarif'             => 45.00,
                'status'            => 'Ouvert',
                'type'              => 'Relais',
                'is_challenge'      => true,
                'is_dossard'        => true,
                'is_avertissement'  => true,
                'is_actif'          => true,
                'max_inscription'   => 500,
                'premier_dossard'   => 1,
                'dernier_dossard'   => 500,
                'distance'          => 10.0,
                'heure_depart'      => '20:00:00',
                'heure_fin'         => '23:00:00',
                'age_minimum'       => 16,
                'age_maximum'       => null,
            ],
            [
                'id_evenement'      => $nocturne->id,
                'nom'               => 'Nocturne des Evaux - 5km Populaire',
                'date_debut'        => '2026-04-10',
                'date_fin'          => '2026-04-10',
                'debut_inscription' => '2026-01-01',
                'fin_inscription'   => '2026-04-05',
                'tarif'             => 20.00,
                'status'            => 'Ouvert',
                'type'              => 'Route',
                'is_challenge'      => false,
                'is_dossard'        => true,
                'is_avertissement'  => false,
                'is_actif'          => true,
                'max_inscription'   => 300,
                'premier_dossard'   => 1001,
                'dernier_dossard'   => 1300,
                'distance'          => 5.0,
                'heure_depart'      => '19:00:00',
                'heure_fin'         => '21:00:00',
                'age_minimum'       => 12,
                'age_maximum'       => null,
            ],
        ];

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['nom' => $courseData['nom']],
                $courseData
            );
        }
    }
}