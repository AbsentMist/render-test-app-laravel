<?php

/**
 * @fileoverview CategorieSeeder.php
 * @description Initialise les catégories et sous-catégories de courses réutilisables.
 *              Catégories : Mixte, Homme, Femme.
 *              Sous-catégories : Etudiant, Employé, Vétéran, Junior.
 * @author Neris Alessandro
 */

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $sousCategories = [
            [
                'nom' => 'Etudiant',
                'modele' => true
            ],
            [
                'nom' => 'Employé',
                'modele' => true
            ],
            [
                'nom' => 'Vétéran',
                'modele' => true
            ],
            [
                'nom' => 'Junior',
                'modele' => true
            ],
        ];

        foreach ($sousCategories as $sousCategorie) {
            DB::table('SousCategorie')->updateOrInsert(
                ['nom' => $sousCategorie['nom']],
                $sousCategorie
            );
        }

        $categories = [
            [
                'nom' => 'Mixte',
                'modele' => true
            ],
            [
                'nom' => 'Homme',
                'modele' => true
            ],
            [
                'nom' => 'Femme',
                'modele' => true
            ],
        ];

        foreach ($categories as $categorie) {
            DB::table('Categorie')->updateOrInsert(
                ['nom' => $categorie['nom']],
                $categorie
            );
        }
    }
}