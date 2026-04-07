<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EvenementTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer le rôle Administrateur
        $roleId = DB::table('Role')->insertGetId(['type' => 'Administrateur']);

        // Créer un user admin
        $userId = DB::table('User')->insertGetId([
            'email'    => 'admin@test.ch',
            'password' => Hash::make('password'),
        ]);

        // Lier le rôle
        DB::table('UserRole')->insert([
            'id_user' => $userId,
            'id_role' => $roleId,
        ]);

        DB::table('Administrateur')->insert(['id_user' => $userId]);

        $this->admin = User::find($userId);
    }

    // ===== INDEX ADMIN =====

    public function test_admin_can_get_all_evenements()
    {
        $this->seed(\Database\Seeders\EvenementSeeder::class);

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/evenements');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_get_evenements()
    {
        $response = $this->getJson('/api/organisateur/evenements');

        $response->assertStatus(401);
    }

    // ===== SHOW =====

    public function test_admin_can_get_single_evenement()
    {
        $evenement = Evenement::create([
           'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/evenements/{$evenement->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Course des Ponts 2025']);
    }

    public function test_show_evenement_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/evenements/999');

        $response->assertStatus(404);
    }

    // ===== STORE =====

    public function test_admin_can_create_evenement()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/evenements', [
                            'nom'                => 'Course des Ponts 2025',
                            'site'               => 'https://coursedesponts.ch',
                            'couleur_primaire'   => '#ff0000',
                            'couleur_secondaire' => '#ffffff',
                            'is_actif'           => true,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'Course des Ponts 2025']);

        $this->assertDatabaseHas('Evenement', ['nom' => 'Course des Ponts 2025']);
    }

    public function test_create_evenement_fails_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/evenements', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nom']);
    }

    // ===== UPDATE =====

    public function test_admin_can_update_evenement()
    {
        $evenement = Evenement::create([
            'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/organisateur/evenements/{$evenement->id}", [
                             'nom' => 'Course des Ponts 2026',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Course des Ponts 2026']);

        $this->assertDatabaseHas('Evenement', ['nom' => 'Course des Ponts 2026']);
    }

    public function test_update_evenement_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson('/api/organisateur/evenements/999', ['nom' => 'Test']);

        $response->assertStatus(404);
    }

    // ===== DESTROY =====

    public function test_admin_can_delete_evenement()
    {
        $evenement = Evenement::create([
            'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/evenements/{$evenement->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Évènement supprimé avec succès.']);

        $this->assertDatabaseMissing('Evenement', ['id' => $evenement->id]);
    }

    public function test_delete_evenement_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson('/api/organisateur/evenements/999');

        $response->assertStatus(404);
    }

    // ===== INDEX PARTICIPANT =====

    public function test_participant_can_get_active_evenements_only()
    {
        Evenement::create([
            'nom'                => 'Evenement actif',
            'site'               => 'https://actif.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);
        Evenement::create([
            'nom'                => 'Evenement inactif',
            'site'               => 'https://inactif.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => false,
        ]);

        $response = $this->actingAs($this->admin)
                        ->getJson('/api/participant/evenements');

        $response->assertStatus(200)
                ->assertJsonFragment(['nom' => 'Evenement actif'])
                ->assertJsonMissing(['nom' => 'Evenement inactif']);
    }

    public function test_participant_cannot_access_evenements_without_auth()
    {
        $response = $this->getJson('/api/participant/evenements');

        $response->assertStatus(401);
    }

    public function test_participant_gets_only_selected_fields()
    {
        Evenement::create([
            'nom'                => 'Evenement test',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $response = $this->actingAs($this->admin)
                        ->getJson('/api/participant/evenements');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => ['id', 'nom', 'site', 'couleur_primaire', 'couleur_secondaire']
                ]);
    }
}