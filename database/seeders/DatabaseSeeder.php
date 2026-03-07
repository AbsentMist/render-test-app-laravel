<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            CategorieSeeder::class,      
            AvertissementSeeder::class,
            EvenementSeeder::class,
            CourseSeeder::class,
            OptionSeeder::class,
            QuestionSeeder::class,       
            InitialDataSeeder::class,
        ]);
    }
}