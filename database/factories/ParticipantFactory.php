<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'id_user' => User::factory(),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'date_naissance' => fake()->date('Y-m-d', '2005-12-31'),
            'equipe_nom' => fake()->optional()->company(),
            'adresse' => fake()->streetAddress(),
            'code_postal' => fake()->postcode(),
            'ville' => fake()->city(),
            'pays' => fake()->country(),
            'telephone' => fake()->unique()->numerify('0#########'),
            'nationalite' => fake()->country(),
            'instagram' => fake()->optional()->userName(),
            'facebook' => fake()->optional()->userName(),
            'taille_tshirt' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'sexe' => fake()->randomElement(['H', 'F', 'Autre']),
            'photo' => null,
        ];
    }
}