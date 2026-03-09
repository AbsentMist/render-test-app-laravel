<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Challenge étudiant'],
            ['nom' => 'Open'],
            ['nom' => 'Vétéran'],
            ['nom' => 'Junior'],
        ];

        foreach ($categories as $categorie) {
            DB::table('Categorie')->updateOrInsert(
                ['nom' => $categorie['nom']],
                $categorie
            );
        }

        $sousCategories = [
            ['nom' => 'Mixte'],
            ['nom' => 'Homme'],
            ['nom' => 'Femme'],
        ];

        foreach ($sousCategories as $sousCategorie) {
            DB::table('SousCategorie')->updateOrInsert(
                ['nom' => $sousCategorie['nom']],
                $sousCategorie
            );
        }
    }
}