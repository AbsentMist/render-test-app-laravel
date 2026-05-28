<?php

/**
 * @fileoverview RoleSeeder.php
 * @description Initialise les rôles de base de l'application.
 *              Doit être exécuté en premier car AdminSeeder en dépend.
 * @author Ngoie Steven
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Role')->insertOrIgnore([
            ['type' => 'Participant'],
            ['type' => 'Administrateur'],
            ['type' => 'Membre']
        ]);
    }
}