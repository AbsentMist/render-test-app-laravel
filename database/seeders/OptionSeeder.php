<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;
use App\Models\OptionQuantifiable;
use App\Models\OptionCochable;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Options de type 'Quantifiable' (Pasta)
        $optionsQuantifiables = [
            [
                'nom' => '1 Entrée + 1 pasta bolognaise',
                'description' => 'Réservation entrée + pasta non-participant CHF 19,00 / paiement à RUNNINGENEVA ASSOCIATION',
                'tarif' => 15.00,
                'qte_min' => 1,
                'qte_max' => 10,
            ],
            [
                'nom' => '1 Entrée + 1 pasta pesto',
                'description' => 'Réservation entrée + pasta non-participant CHF 19,00 / paiement à RUNNINGENEVA ASSOCIATION',
                'tarif' => 15.00,
                'qte_min' => 1,
                'qte_max' => 10,
            ],
        ];

        foreach ($optionsQuantifiables as $data) {
            // On gère la table parente 'Option'
            $option = Option::updateOrCreate(
                ['nom' => $data['nom']], // Clé unique
                [
                    'description' => $data['description'],
                    'tarif'       => $data['tarif'],
                    'type'        => 'Quantifiable',
                    'modele'      => true,
                ]
            );

            // On gère la table fille 'OptionQuantifiable'
            OptionQuantifiable::updateOrCreate(
                ['id' => $option->id], // L'ID de l'option parente
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
    }
}