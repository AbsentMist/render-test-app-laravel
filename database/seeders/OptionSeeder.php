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
        // 1. Définition des options de type 'Quantifiable' (Pasta)
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
            $option = Option::create([
                'nom'         => $data['nom'],
                'description' => $data['description'],
                'tarif'       => $data['tarif'],
                'type'        => 'Quantifiable',
                'modele'      => true,
            ]);

            OptionQuantifiable::create([
                'id'          => $option->id, //
                'quantiteMin' => $data['qte_min'],
                'quantiteMax' => $data['qte_max'],
            ]);
        }

        // 2. Définition de l'option de type 'Cochable' (Navettes)
        $navette = Option::create([
            'nom'         => "J'utilise les navettes transports de l'organisation",
            'description' => "Transport organisé par l'événement",
            'tarif'       => 2.00,
            'type'        => 'Cochable',
            'modele'      => true,
        ]);

        OptionCochable::create([
            'id'       => $navette->id, //
            'is_coche' => false,
        ]);
    }
}