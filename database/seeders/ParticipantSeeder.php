<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParticipantSeeder extends Seeder
{
    /**
     * Exécute les seeds pour les participants
     *
     * Crée un utilisateur de test et un participant associé.
     *
     * @return void
     */
    public function run(): void
    {
        // Participant 1
        $user1 = User::create([
            'email'    => 'particip1@inscriptionrunning.ch',
            'password' => Hash::make('Particip123#'),
        ]);

        Participant::create([
            'id_user'        => $user1->id,
            'nom'            => 'Dupond',
            'prenom'         => 'Jean',
            'date_naissance' => '1990-05-15',
            'equipe_nom'     => null,
            'adresse'        => '123 Rue de Genève',
            'code_postal'    => '1200',
            'ville'          => 'Genève',
            'pays'           => 'Suisse',
            'telephone'      => '+41791234567',
            'nationalite'    => 'Suisse',
            'instagram'      => null,
            'facebook'       => null,
            'taille_tshirt'  => 'M',
            'sexe'           => 'Homme',
            'photo'          => null,
        ]);

        // Participant 2
        $user2 = User::create([
            'email'    => 'particip2@inscriptionrunning.ch',
            'password' => Hash::make('Particip123#'),
        ]);

        Participant::create([
            'id_user'        => $user2->id,
            'nom'            => 'Durand',
            'prenom'         => 'Marie',
            'date_naissance' => '1995-03-20',
            'equipe_nom'     => null,
            'adresse'        => '456 Avenue du Lac',
            'code_postal'    => '1201',
            'ville'          => 'Genève',
            'pays'           => 'Suisse',
            'telephone'      => '+41791234568',
            'nationalite'    => 'Suisse',
            'instagram'      => null,
            'facebook'       => null,
            'taille_tshirt'  => 'L',
            'sexe'           => 'Femme',
            'photo'          => null,
        ]);
    }
}
