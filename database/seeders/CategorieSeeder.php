<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nom' => 'Challenge étudiant',
                'modele' => true
            ],
            [
                'nom' => 'Open',
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

        foreach ($categories as $categorie) {
            DB::table('Categorie')->updateOrInsert(
                ['nom' => $categorie['nom']],
                $categorie
            );
        }

        $sousCategories = [
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

        foreach ($sousCategories as $sousCategorie) {
            DB::table('SousCategorie')->updateOrInsert(
                ['nom' => $sousCategorie['nom']],
                $sousCategorie
            );
        }
    }
}