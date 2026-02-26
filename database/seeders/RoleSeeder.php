<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Role')->insertOrIgnore([
            ['type' => 'Participant'],
            ['type' => 'Administrateur']
        ]);
    }
}