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

        // 2. Liste de l'équipe avec infos participant
        $admins = [
            // Mandante
            [
                'email'     => 'pb@runningeneva.ch',
                'password'  => 'AdminRGVA2026!',
                'prenom'    => 'Patricia',
                'nom'       => 'Bongini',
                'sexe'      => 'F',
                'telephone' => '0000000001',
            ],
            // Membres de l'équipe
            [
                'email'     => 'alessandro.neris@hes-so.ch',
                'password'  => 'AdminRGVA2026!',
                'prenom'    => 'Alessandro',
                'nom'       => 'Neris',
                'sexe'      => 'M',
                'telephone' => '0000000002',
            ],
            [
                'email'     => 'remi.perroud@hes-so.ch',
                'password'  => 'AdminRGVA2026!',
                'prenom'    => 'Rémi',
                'nom'       => 'Perroud',
                'sexe'      => 'M',
                'telephone' => '0000000003',
            ],
            [
                'email'     => 'jean-daniel.guillermet-suarez@hes-so.ch',
                'password'  => 'AdminRGVA2026!',
                'prenom'    => 'Jean-Daniel',
                'nom'       => 'Guillermet',
                'sexe'      => 'M',
                'telephone' => '0000000004',
            ],
            [
                'email'     => 'steven.ngoie@hes-so.ch',
                'password'  => 'AdminRGVA2026!',
                'prenom'    => 'Steven',
                'nom'       => 'Ngoie',
                'sexe'      => 'M',
                'telephone' => '0000000005',
            ],
        ];

        foreach ($admins as $admin) {
            // Vérification si l'utilisateur existe déjà
            $existingUser = DB::table('User')->where('email', $admin['email'])->first();

            if (!$existingUser) {
                // Création dans la table User
                $userId = DB::table('User')->insertGetId([
                    'email'    => $admin['email'],
                    'password' => Hash::make($admin['password']),
                ]);

                // Ajout dans la table d'association UserRole
                DB::table('UserRole')->insert([
                    'id_user' => $userId,
                    'id_role' => $roleAdmin->id,
                ]);

                // Ajout dans la table Administrateur
                DB::table('Administrateur')->insert([
                    'id_user' => $userId,
                ]);

                // Ajout dans la table Participant
                DB::table('Participant')->insert([
                    'id_user'        => $userId,
                    'prenom'         => $admin['prenom'],
                    'nom'            => $admin['nom'],
                    'sexe'           => $admin['sexe'],
                    'telephone'      => $admin['telephone'],
                    'date_naissance' => '1990-01-01',
                    'adresse'        => 'Rue de Genève 1',
                    'code_postal'    => '1200',
                    'ville'          => 'Genève',
                    'pays'           => 'Suisse',
                    'nationalite'    => 'Suisse',
                    'taille_tshirt'  => 'M',
                ]);
            }
        }
    }
}