<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'id_user'        => User::factory(), 
            
            'nom'            => fake('fr_CH')->lastName(),
            'prenom'         => fake('fr_CH')->firstName(),
            'date_naissance' => fake()->date('Y-m-d', '-18 years'),
            
            // Données en suisses
            'adresse'        => fake('fr_CH')->streetAddress(),
            'code_postal'    => fake('fr_CH')->postcode(),
            'ville'          => fake('fr_CH')->city(),
            'pays'           => 'Suisse', 
            
            // Format suisse (+41...)
            'telephone'      => fake('fr_CH')->e164PhoneNumber(), 
            
            'nationalite'    => 'Suisse',
            'taille_tshirt'  => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'sexe'           => fake()->randomElement(['Homme', 'Femme']),
        ];
    }
}