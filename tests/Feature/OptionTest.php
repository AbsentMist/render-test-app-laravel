<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OptionTest extends TestCase
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

    public function test_admin_can_get_all_options()
    {
        // Crée un modèle et une non-modèle
        Option::create(['nom' => 'Option modèle', 'tarif' => 10, 'type' => 'Cochable', 'description' => 'Test', 'modele' => true]);
        Option::create(['nom' => 'Option non modèle', 'tarif' => 5, 'type' => 'Cochable', 'description' => 'Test', 'modele' => false]);

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/options');

        // Seuls les modele=true doivent être retournés
        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_non_admin_cannot_get_options()
    {
        $response = $this->getJson('/api/organisateur/options');

        $response->assertStatus(401);
    }

    // ===== SHOW =====

    public function test_admin_can_get_single_option()
    {
        $option = Option::create([
            'nom'         => 'T-shirt',
            'tarif'       => 20,
            'type'        => 'Cochable',
            'description' => 'Un t-shirt',
            'modele'      => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/options/{$option->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'T-shirt']);
    }

    public function test_show_option_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/options/999');

        $response->assertStatus(404);
    }

    // ===== STORE =====

    public function test_admin_can_create_option_cochable()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/options', [
                             'nom'         => 'T-shirt',
                             'tarif'       => 20,
                             'type'        => 'Cochable',
                             'description' => 'Un t-shirt',
                             'modele'      => true,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'T-shirt']);

        $this->assertDatabaseHas('options', ['nom' => 'T-shirt']);
    }

    public function test_admin_can_create_option_quantifiable()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/options', [
                             'nom'         => 'Repas',
                             'tarif'       => 15,
                             'type'        => 'Quantifiable',
                             'description' => 'Nombre de repas',
                             'modele'      => true,
                             'quantiteMin' => 1,
                             'quantiteMax' => 5,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'Repas']);

        $this->assertDatabaseHas('options', ['nom' => 'Repas']);
    }

    public function test_create_option_fails_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/options', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nom', 'tarif', 'type', 'description']);
    }

    public function test_create_quantifiable_option_fails_without_quantite()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/options', [
                             'nom'         => 'Repas',
                             'tarif'       => 15,
                             'type'        => 'Quantifiable',
                             'description' => 'Nombre de repas',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantiteMin', 'quantiteMax']);
    }

    // ===== UPDATE =====

    public function test_admin_can_update_option()
    {
        $option = Option::create([
            'nom'         => 'Ancien nom',
            'tarif'       => 10,
            'type'        => 'Cochable',
            'description' => 'Description',
            'modele'      => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/organisateur/options/{$option->id}", [
                             'nom' => 'Nouveau nom',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Nouveau nom']);

        $this->assertDatabaseHas('options', ['nom' => 'Nouveau nom']);
    }

    public function test_update_option_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson('/api/organisateur/options/999', ['nom' => 'Test']);

        $response->assertStatus(404);
    }

    // ===== DESTROY =====

    public function test_admin_can_delete_option()
    {
        $option = Option::create([
            'nom'         => 'A supprimer',
            'tarif'       => 10,
            'type'        => 'Cochable',
            'description' => 'Description',
            'modele'      => true,
        ]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/options/{$option->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Option supprimée avec succès.']);

        $this->assertDatabaseMissing('options', ['id' => $option->id]);
    }

    public function test_delete_option_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson('/api/organisateur/options/999');

        $response->assertStatus(404);
    }


    // ===== INDEX PARTICIPANT =====

    public function test_participant_can_get_options_for_a_course()
    {
        $evenement = DB::table('Evenement')->insertGetId([
           'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        // Créer une course et une option liée
        $courseId = DB::table('Course')->insertGetId([
            'id_evenement'      => $evenement,
            'nom'               => 'A supprimer',
            'is_actif'          => true,
            'tarif'             => 35,
            'date_debut'        => '2025-05-15',
            'date_fin'          => '2025-05-15',
            'debut_inscription' => '2025-01-01',
            'fin_inscription'   => '2025-05-10',
            'status'            => 'Ouvert',
            'type'              => 'Route',
            'max_inscription'   => 500,
            'premier_dossard'   => 1,
            'dernier_dossard'   => 500,
            'age_minimum'       => 16,
        ]);

        $option = Option::create([
            'nom'         => 'T-shirt',
            'tarif'       => 20,
            'type'        => 'Cochable',
            'description' => 'Un t-shirt',
            'modele'      => false,
        ]);

        // Lier l'option à la course
        DB::table('OptionPourCourse')->insert([
            'id_course' => $courseId,
            'id_option' => $option->id,
        ]);

        $response = $this->actingAs($this->admin)
                        ->getJson("/api/participant/options/{$courseId}");

        $response->assertStatus(200)
                ->assertJsonFragment(['nom' => 'T-shirt']);
    }

    public function test_participant_gets_404_if_no_options_for_course()
    {
        $evenement = DB::table('Evenement')->insertGetId([
           'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $courseId = DB::table('Course')->insertGetId([
            'id_evenement'      => $evenement,
            'nom'               => 'A supprimer',
            'is_actif'          => true,
            'tarif'             => 35,
            'date_debut'        => '2025-05-15',
            'date_fin'          => '2025-05-15',
            'debut_inscription' => '2025-01-01',
            'fin_inscription'   => '2025-05-10',
            'status'            => 'Ouvert',
            'type'              => 'Route',
            'max_inscription'   => 500,
            'premier_dossard'   => 1,
            'dernier_dossard'   => 500,
            'age_minimum'       => 16,
        ]);

        $response = $this->actingAs($this->admin)
                        ->getJson("/api/participant/options/{$courseId}");

        $response->assertStatus(404)
                ->assertJsonFragment(['message' => 'Aucune option disponible pour cette course.']);
    }
}