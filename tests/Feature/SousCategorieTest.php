<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\SousCategorie;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SousCategorieTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Evenement $evenement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $this->evenement = Evenement::create([
            'nom'                => 'Evenement test sous-categorie',
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
            'nom'               => 'Course sous-categorie test',
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

    public function test_admin_can_get_all_sous_categories()
    {
        SousCategorie::create(['nom' => 'Sous-categorie modele unique', 'modele' => true]);
        SousCategorie::create(['nom' => 'Sous-categorie non modele unique', 'modele' => false]);

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/sous-categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Sous-categorie modele unique'])
                 ->assertJsonMissing(['nom' => 'Sous-categorie non modele unique']);
    }

    public function test_non_admin_cannot_get_sous_categories()
    {
        $response = $this->getJson('/api/organisateur/sous-categories');

        $response->assertStatus(401);
    }

    public function test_participant_can_get_sous_categories_for_course()
    {
        $sousCategorie = SousCategorie::create(['nom' => 'Sous-categorie liée au test', 'modele' => false]);
        $course = $this->createCourse(['id_sous_categorie' => $sousCategorie->id, 'nom' => 'Course avec sous-categorie test']);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/participant/sous-categories/{$course->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Sous-categorie liée au test']);
    }

    public function test_participant_gets_404_if_no_sous_categories_for_course()
    {
        $course = $this->createCourse(['nom' => 'Course sans sous-categorie', 'id_sous_categorie' => null]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/participant/sous-categories/{$course->id}");

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Aucune sous categorie disponible pour cette course.']);
    }

    public function test_admin_can_get_single_sous_categorie()
    {
        $sousCategorie = SousCategorie::create(['nom' => 'Sous-categorie unique', 'modele' => true]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/sous-categories/{$sousCategorie->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Sous-categorie unique']);
    }

    public function test_show_sous_categorie_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/sous-categories/999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_sous_categorie()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/sous-categories', [
                             'nom'    => 'Nouvelle sous-categorie',
                             'modele' => true,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'Nouvelle sous-categorie']);

        $this->assertDatabaseHas('SousCategorie', ['nom' => 'Nouvelle sous-categorie']);
    }

    public function test_create_sous_categorie_fails_without_nom()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/sous-categories', [
                             'modele' => true,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nom']);
    }

    public function test_admin_can_update_sous_categorie()
    {
        $sousCategorie = SousCategorie::create(['nom' => 'Ancienne sous-categorie', 'modele' => true]);

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/organisateur/sous-categories/{$sousCategorie->id}", [
                             'nom' => 'Sous-categorie mise a jour',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Sous-categorie mise a jour']);

        $this->assertDatabaseHas('SousCategorie', ['nom' => 'Sous-categorie mise a jour']);
    }

    public function test_update_sous_categorie_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson('/api/organisateur/sous-categories/999', [
                             'nom' => 'Test',
                         ]);

        $response->assertStatus(404);
    }

    public function test_admin_can_delete_sous_categorie()
    {
        $sousCategorie = SousCategorie::create(['nom' => 'Sous-categorie a supprimer', 'modele' => true]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/sous-categories/{$sousCategorie->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Sous-catégorie supprimée avec succès.']);

        $this->assertDatabaseMissing('SousCategorie', ['id' => $sousCategorie->id]);
    }

    public function test_delete_sous_categorie_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson('/api/organisateur/sous-categories/999');

        $response->assertStatus(404);
    }
}