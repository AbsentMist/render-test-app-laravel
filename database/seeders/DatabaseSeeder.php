<?php

/**
 * @fileoverview DatabaseSeeder.php
 * @description Point d'entrée principal du seeding. Orchestre l'exécution de tous
 *              les seeders dans le bon ordre de dépendance.
 *              À exécuter via : php artisan migrate:fresh --seed
 * @author Ngoie Steven
 */

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
            ParticipantSeeder::class,
            CategorieSeeder::class,
            AvertissementSeeder::class,
            EvenementSeeder::class,
            CourseSeeder::class,
            PrixEvolutifSeeder::class,
            OptionSeeder::class,
            QuestionSeeder::class,
            CodeRabaisSeeder::class,
            CodeDossardSeeder::class,
            InscriptionSeeder::class,
            InitialDataSeeder::class,
        ]);
    }
}
