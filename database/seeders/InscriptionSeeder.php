<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Inscription;
use App\Models\User;

class InscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère tous les participants
        $participants = User::has('participant')->with('participant')->get();

        if ($participants->isEmpty()) {
           return;
        }

        // Récupère toutes les courses actives
        $courses = Course::where('is_actif', true)->get();

        if ($courses->isEmpty()) {
            return;
        }

        $statuts = ['Validé', 'En attente', 'Annulé'];

        foreach ($participants as $user) {
            if (!$user->participant) continue;

            // Chaque participant s'inscrit à 1-3 courses aléatoires
            $coursesAleatoires = $courses->random(min(rand(1, 3), $courses->count()));

            foreach ($coursesAleatoires as $course) {
                $statut = $statuts[array_rand($statuts)];

                Inscription::updateOrCreate(
                    [
                        'id_participant' => $user->participant->id,
                        'id_course'      => $course->id,
                    ],
                    [
                        'tarif'               => $course->tarif,
                        'status_paiement'     => $statut,
                        'montant_rabais'      => 0,
                        'avertissement_valide'=> $course->is_avertissement ? true : false,
                        'id_groupe'           => null,
                        'id_document'         => null,
                        'code_participant'    => null,
                    ]
                );
            }
        }

    }
}