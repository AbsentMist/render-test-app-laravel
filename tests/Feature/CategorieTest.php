<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Course;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategorieTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Evenement $evenement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $this->evenement = Evenement::create([
            'nom'                => 'Evenement test categorie',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);
    }

    protected function createAdminUser(): User
    {
        $roleId = DB::table('Role')->where('type', 'Administrateur')->value('id');

        if (!$roleId) {
            $roleId = DB::table('Role')->insertGetId(['type' => 'Administrateur']);
        }

        $userId = DB::table('User')->insertGetId([
            'email'    => 'admin_' . uniqid() . '@test.ch',
            'password' => Hash::make('password'),
        ]);

        DB::table('UserRole')->insert([
            'id_user' => $userId,
            'id_role' => $roleId,
        ]);

        DB::table('Administrateur')->insert(['id_user' => $userId]);

        return User::findOrFail($userId);
    }

    protected function createCourse(array $overrides = []): Course
    {
        $baseData = [
            'id_evenement'      => $this->evenement->id,
            'nom'               => 'Course categorie test',
            'date_debut'        => '2027-05-15',
            'date_fin'          => '2027-05-15',
            'debut_inscription' => '2027-01-01',
            'fin_inscription'   => '2027-05-10',
            'tarif'             => 35,
            'status'            => 'Ouvert',
            'type'              => 'Route',
            'max_inscription'   => 100,
            'premier_dossard'   => 1,
            'dernier_dossard'   => 100,
            'age_minimum'       => 16,
            'is_actif'          => true,
        ];

        return Course::create(array_merge($baseData, $overrides));
    }

    public function test_admin_can_get_all_categories()
    {
        Categorie::create(['nom' => 'Categorie modèle unique', 'modele' => true]);
        Categorie::create(['nom' => 'Categorie non modèle unique', 'modele' => false]);

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Categorie modèle unique'])
                 ->assertJsonMissing(['nom' => 'Categorie non modèle unique']);
    }

    public function test_non_admin_cannot_get_categories()
    {
        $response = $this->getJson('/api/organisateur/categories');

        $response->assertStatus(401);
    }

    public function test_participant_can_get_categories_for_course()
    {
        $categorie = Categorie::create(['nom' => 'Categorie liée au test', 'modele' => false]);
        $this->createCourse(['id_categorie' => $categorie->id, 'nom' => 'Course avec categorie test']);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/participant/categories/{$categorie->courses()->first()->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Categorie liée au test']);
    }

    public function test_participant_gets_404_if_no_categories_for_course()
    {
        $course = $this->createCourse(['nom' => 'Course sans categorie', 'id_categorie' => null]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/participant/categories/{$course->id}");

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Aucune categorie disponible pour cette course.']);
    }

    public function test_admin_can_get_single_category()
    {
        $categorie = Categorie::create(['nom' => 'Categorie unique', 'modele' => true]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/categories/{$categorie->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Categorie unique']);
    }

    public function test_show_category_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/categories/999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/categories', [
                             'nom'    => 'Nouvelle categorie',
                             'modele' => true,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'Nouvelle categorie']);

        $this->assertDatabaseHas('Categorie', ['nom' => 'Nouvelle categorie']);
    }

    public function test_create_category_fails_without_nom()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/categories', [
                             'modele' => true,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nom']);
    }

    public function test_admin_can_update_category()
    {
        $categorie = Categorie::create(['nom' => 'Ancienne categorie', 'modele' => true]);

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/organisateur/categories/{$categorie->id}", [
                             'nom' => 'Categorie mise a jour',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Categorie mise a jour']);

        $this->assertDatabaseHas('Categorie', ['nom' => 'Categorie mise a jour']);
    }

    public function test_update_category_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson('/api/organisateur/categories/999', [
                             'nom' => 'Test',
                         ]);

        $response->assertStatus(404);
    }

    public function test_admin_can_delete_category()
    {
        $categorie = Categorie::create(['nom' => 'Categorie a supprimer', 'modele' => true]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/categories/{$categorie->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Catégorie supprimée avec succès.']);

        $this->assertDatabaseMissing('Categorie', ['id' => $categorie->id]);
    }

    public function test_delete_category_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson('/api/organisateur/categories/999');

        $response->assertStatus(404);
    }
}