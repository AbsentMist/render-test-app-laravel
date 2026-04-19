<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $admins = [
            [
                'email'     => 'pb@runningeneva.ch',
                'prenom'    => 'Patricia',
                'nom'       => 'Bongini',
                'sexe'      => 'F',
                'telephone' => '0000000001',
            ],
            [
                'email'     => 'alessandro.neris@hes-so.ch',
                'prenom'    => 'Alessandro',
                'nom'       => 'Neris',
                'sexe'      => 'M',
                'telephone' => '0000000002',
            ],
            [
                'email'     => 'remi.perroud@hes-so.ch',
                'prenom'    => 'Rémi',
                'nom'       => 'Perroud',
                'sexe'      => 'M',
                'telephone' => '0000000003',
            ],
            [
                'email'     => 'jean-daniel.guillermet-suarez@hes-so.ch',
                'prenom'    => 'Jean-Daniel',
                'nom'       => 'Guillermet',
                'sexe'      => 'M',
                'telephone' => '0000000004',
            ],
            [
                'email'     => 'steven.ngoie@hes-so.ch',
                'prenom'    => 'Steven',
                'nom'       => 'Ngoie',
                'sexe'      => 'M',
                'telephone' => '0000000005',
            ],
        ];

        foreach ($admins as $admin) {
            $user = DB::table('User')->where('email', $admin['email'])->first();

            if (!$user) continue;

            // Ajouter le Participant seulement s'il n'existe pas déjà
            $exists = DB::table('Participant')->where('id_user', $user->id)->exists();
            if (!$exists) {
                DB::table('Participant')->insert([
                    'id_user'        => $user->id,
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

    public function down(): void
    {
        // On ne supprime pas les participants en rollback
    }
};
