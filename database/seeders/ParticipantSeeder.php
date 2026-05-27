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
        // Créer un utilisateur
        $user = User::create([
            'email'    => 'participant@test.ch',
            'password' => Hash::make('Participant2026!'),
        ]);

        // Créer un participant lié à cet utilisateur
        Participant::create([
            'id_user'        => $user->id,
            'nom'            => 'Dupont',
            'prenom'         => 'Jean',
            'date_naissance' => '1990-05-15',
            'equipe_nom'     => null,
            'adresse'        => '123 Rue de la Paix',
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
    }
}
