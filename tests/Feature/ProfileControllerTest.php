<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_show_returns_profile_payload_for_authenticated_user()
    {
        $user = User::create([
            'email' => 'profile_show_' . uniqid() . '@test.ch',
            'password' => Hash::make('StrongPwd!123'),
        ]);

        Participant::factory()->create([
            'id_user' => $user->id,
            'nom' => 'Santos',
            'prenom' => 'Romeo',
            'date_naissance' => '2000-01-01',
            'adresse' => 'Rue de Carouge 14A',
            'code_postal' => '1227',
            'ville' => 'Carouge',
            'nationalite' => 'Suisse',
            'telephone' => '078 777 47 58',
            'taille_tshirt' => 'M',
        ]);

        $response = $this->actingAs($user)->getJson('/api/participant/profil');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nom' => 'Santos',
                'prenom' => 'Romeo',
                'email' => $user->email,
                'adresse' => 'Rue de Carouge',
                'numero' => '14A',
                'npa' => '1227',
                'commune' => 'Carouge',
                'nationalite' => 'Suisse',
                'telephone' => '078 777 47 58',
                'tailleTshirt' => 'M',
            ]);
    }

    public function test_show_returns_404_when_user_has_no_participant()
    {
        $user = User::create([
            'email' => 'profile_404_' . uniqid() . '@test.ch',
            'password' => Hash::make('StrongPwd!123'),
        ]);

        $response = $this->actingAs($user)->getJson('/api/participant/profil');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Participant introuvable.']);
    }

    public function test_update_updates_user_and_participant_profile_fields()
    {
        $user = User::create([
            'email' => 'profile_update_before_' . uniqid() . '@test.ch',
            'password' => Hash::make('StrongPwd!123'),
        ]);

        $participant = Participant::factory()->create([
            'id_user' => $user->id,
            'telephone' => '078 111 11 11',
            'adresse' => 'Rue Ancienne 1',
        ]);

        $payload = [
            '_method' => 'PUT',
            'nom' => 'Benoit',
            'prenom' => 'Patrice',
            'email' => 'profile_update_after_' . uniqid() . '@test.ch',
            'dateNaissance' => '01/02/2000',
            'adresse' => 'Rue de Carouge',
            'numero' => '14',
            'club' => 'Running Club',
            'npa' => '1227',
            'commune' => 'Carouge',
            'nationalite' => 'Suisse',
            'telephone' => '078 777 47 58',
            'tailleTshirt' => 'L',
            'photo' => UploadedFile::fake()->image('profile.jpg'),
        ];

        $response = $this->actingAs($user)->post('/api/participant/profil', $payload, ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nom' => 'Benoit',
                'prenom' => 'Patrice',
                'adresse' => 'Rue de Carouge',
                'numero' => '14',
                'npa' => '1227',
                'commune' => 'Carouge',
                'telephone' => '078 777 47 58',
                'tailleTshirt' => 'L',
            ]);

        $this->assertDatabaseHas('User', [
            'id' => $user->id,
            'email' => $payload['email'],
        ]);

        $this->assertDatabaseHas('Participant', [
            'id' => $participant->id,
            'nom' => 'Benoit',
            'prenom' => 'Patrice',
            'adresse' => 'Rue de Carouge 14',
            'code_postal' => '1227',
            'ville' => 'Carouge',
            'nationalite' => 'Suisse',
            'telephone' => '078 777 47 58',
            'taille_tshirt' => 'L',
            'equipe_nom' => 'Running Club',
        ]);

        $this->assertNotNull($participant->fresh()->photo);
    }

    public function test_update_returns_validation_error_for_invalid_payload()
    {
        $user = User::create([
            'email' => 'profile_validation_' . uniqid() . '@test.ch',
            'password' => Hash::make('StrongPwd!123'),
        ]);

        Participant::factory()->create([
            'id_user' => $user->id,
            'telephone' => '078 222 22 22',
        ]);

        $payload = [
            '_method' => 'PUT',
            'nom' => 'Benoit',
            'prenom' => 'Patrice',
            'email' => 'invalid-email',
            'dateNaissance' => '2000-01-01',
            'adresse' => 'Rue de Carouge',
            'numero' => '14',
            'npa' => '1227',
            'commune' => 'Carouge',
            'nationalite' => 'Suisse',
            'telephone' => '078 777 47 58',
            'tailleTshirt' => 'L',
        ];

        $response = $this->actingAs($user)->post('/api/participant/profil', $payload, ['Accept' => 'application/json']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'dateNaissance']);
    }
}
