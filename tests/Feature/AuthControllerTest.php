<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_register_creates_user_and_participant()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'register_' . uniqid() . '@test.ch',
            'password' => 'StrongPwd!123',
            'password_confirmation' => 'StrongPwd!123',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'date_naissance' => '1990-01-01',
            'telephone' => '+4179' . rand(100000, 999999),
            'nationalite' => 'Suisse',
            'adresse' => 'Rue de Test 1',
            'code_postal' => '1200',
            'ville' => 'Geneve',
            'pays' => 'Suisse',
            'taille_tshirt' => 'M',
            'sexe' => 'Homme',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('User', ['email' => $response->json('user.email')]);
        $this->assertDatabaseHas('Participant', ['id_user' => $response->json('user.id')]);
    }

    public function test_login_returns_token_for_valid_credentials()
    {
        $email = 'login_' . uniqid() . '@test.ch';

        $user = User::create([
            'email' => $email,
            'password' => Hash::make('StrongPwd!123'),
        ]);

        Participant::factory()->create(['id_user' => $user->id]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'StrongPwd!123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nobody@test.ch',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_fetch_me()
    {
        $user = User::factory()->create();
        Participant::factory()->create(['id_user' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $user->id]);
    }

    public function test_participant_lookup_by_email_returns_404_if_not_found()
    {
        $user = User::factory()->create();
        Participant::factory()->create(['id_user' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/participant/rechercher-participant?email=absent_' . uniqid() . '@test.ch');

        $response->assertStatus(404);
    }

    public function test_participant_lookup_by_email_returns_basic_participant_fields()
    {
        $caller = User::factory()->create();
        Participant::factory()->create(['id_user' => $caller->id]);

        $targetUser = User::create([
            'email' => 'found_' . uniqid() . '@test.ch',
            'password' => Hash::make('StrongPwd!123'),
        ]);
        $targetParticipant = Participant::factory()->create([
            'id_user' => $targetUser->id,
            'prenom' => 'Alice',
            'nom' => 'Martin',
        ]);

        $response = $this->actingAs($caller)
            ->getJson('/api/participant/rechercher-participant?email=' . urlencode($targetUser->email));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $targetParticipant->id,
                'prenom' => 'Alice',
                'nom' => 'Martin',
                'email' => $targetUser->email,
            ]);
    }
}