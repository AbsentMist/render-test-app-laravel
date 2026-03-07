<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Categorie')->insert([
            ['nom' => 'Challenge étudiant'],
            ['nom' => 'Open'],
            ['nom' => 'Vétéran'],
            ['nom' => 'Junior'],
        ]);

        DB::table('SousCategorie')->insert([
            ['nom' => 'Mixte'],
            ['nom' => 'Homme'],
            ['nom' => 'Femme'],
        ]);
    }
}