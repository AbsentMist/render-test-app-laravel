<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. On récupère l'ID du rôle Administrateur
        $roleAdmin = DB::table('Role')->where('type', 'Administrateur')->first();

        if (!$roleAdmin) {
            $this->command->error("Le rôle Administrateur n'existe pas. Lancez le RoleSeeder d'abord !");
            return;
        }

        // 2. Liste de ton équipe (À modifier selon tes besoins)
        $admins = [
            //Mandante
            ['email' => 'pb@runningeneva.ch', 'password' => 'AdminRGVA2026!'],

            //Membres de l'équipe
            ['email' => 'alessandro.neris@hes-so.ch', 'password' => 'AdminRGVA2026!'],
            ['email' => 'remi.perroud@hes-so.ch', 'password' => 'AdminRGVA2026!'],
            ['email' => 'jean-daniel.guillermet-suarez@hes-so.ch', 'password' => 'AdminRGVA2026!'],
            ['email' => 'steven.ngoie@hes-so.ch', 'password' => 'AdminRGVA2026!']
        ];

        foreach ($admins as $admin) {
            // Vérification si  l'utilisateur existe dans User
            $existingUser = DB::table('User')->where('email', $admin['email'])->first();

            if (!$existingUser) {
                // Création dans la table User
                $userId = DB::table('User')->insertGetId([
                    'email' => $admin['email'],
                    'password' => Hash::make($admin['password']),
                    // Pas de timestamps 
                ]);

                //Ajout dans la table d'association UserRole
                DB::table('UserRole')->insert([
                    'id_user' => $userId,
                    'id_role' => $roleAdmin->id
                ]);

                // Ajout dans la table Administrateur
                DB::table('Administrateur')->insert([
                    'id_user' => $userId
                ]);
            }
        }
    }
}