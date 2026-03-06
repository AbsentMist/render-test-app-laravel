<?php

namespace Tests\Feature;

use App\Models\Avertissement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AvertissementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $roleId = DB::table('Role')->insertGetId(['type' => 'Administrateur']);
        $userId = DB::table('User')->insertGetId([
            'email'    => 'admin@test.ch',
            'password' => Hash::make('password'),
        ]);
        DB::table('UserRole')->insert(['id_user' => $userId, 'id_role' => $roleId]);
        DB::table('Administrateur')->insert(['id_user' => $userId]);

        $this->admin = User::find($userId);
    }

    // ===== INDEX ADMIN =====

    public function test_admin_can_get_all_avertissements()
    {
        Avertissement::create(['titre' => 'Test', 'contenu' => 'Contenu', 'modele' => true]);
        Avertissement::create(['titre' => 'Test 2', 'contenu' => 'Contenu 2', 'modele' => false]);

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/avertissements');

        // Seuls les modele=true doivent être retournés
        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_non_admin_cannot_get_avertissements()
    {
        $response = $this->getJson('/api/organisateur/avertissements');

        $response->assertStatus(401);
    }

    // ===== SHOW =====

    public function test_admin_can_get_single_avertissement()
    {
        $avertissement = Avertissement::create([
            'titre'   => 'Verglas',
            'contenu' => 'Attention au verglas.',
            'modele'  => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/avertissements/{$avertissement->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['titre' => 'Verglas']);
    }

    public function test_show_avertissement_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/avertissements/999');

        $response->assertStatus(404);
    }

    // ===== STORE =====

    public function test_admin_can_create_avertissement()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/avertissements', [
                             'titre'   => 'Nouveau avertissement',
                             'contenu' => 'Contenu de test',
                             'modele'  => true,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['titre' => 'Nouveau avertissement']);

        $this->assertDatabaseHas('avertissement', ['titre' => 'Nouveau avertissement']);
    }

    public function test_create_avertissement_fails_without_contenu()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/avertissements', [
                             'titre' => 'Sans contenu',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['contenu']);
    }

    // ===== UPDATE =====

    public function test_admin_can_update_avertissement()
    {
        $avertissement = Avertissement::create([
            'titre'   => 'Ancien titre',
            'contenu' => 'Ancien contenu',
            'modele'  => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/organisateur/avertissements/{$avertissement->id}", [
                             'titre' => 'Nouveau titre',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['titre' => 'Nouveau titre']);

        $this->assertDatabaseHas('avertissement', ['titre' => 'Nouveau titre']);
    }

    public function test_update_avertissement_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson('/api/organisateur/avertissements/999', [
                             'titre' => 'Test',
                         ]);

        $response->assertStatus(404);
    }

    // ===== DESTROY =====

    public function test_admin_can_delete_avertissement()
    {
        $avertissement = Avertissement::create([
            'titre'   => 'A supprimer',
            'contenu' => 'Contenu',
            'modele'  => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/avertissements/{$avertissement->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Modèle d\'avertissement supprimé avec succès.']);

        $this->assertDatabaseMissing('avertissement', ['id' => $avertissement->id]);
    }

    public function test_delete_avertissement_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson('/api/organisateur/avertissements/999');

        $response->assertStatus(404);
    }
}