<?php

/**
 * @fileoverview InitialDataSeeder.php
 * @description Insère le message de bienvenue initial dans la table messages.
 *              Utilisé par MessageController (contrôleur de test, non utilisé en production).
 * @author Ngoie Steven
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('messages')->insert([
            'content' => 'Bienvenue sur la plateforme de test de Render!',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
