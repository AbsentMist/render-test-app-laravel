<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\PrixEvolutif;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrixEvolutifTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $evenement = Evenement::create([
            'nom'                => 'Evenement prix',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course prix',
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

        DB::table('UserRole')->insert(['id_user' => $userId, 'id_role' => $roleId]);
        DB::table('Administrateur')->insert(['id_user' => $userId]);

        return User::findOrFail($userId);
    }

    public function test_index_returns_paliers_for_course()
    {
        PrixEvolutif::create([
            'id_course' => $this->course->id,
            'type' => 'dossards',
            'valeur_debut' => '1',
            'valeur_fin' => '50',
            'tarif' => 30,
            'ordre' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/prix-evolutif");

        $response->assertStatus(200)
            ->assertJsonFragment(['tarif' => 30]);
    }

    public function test_admin_can_create_palier()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/organisateur/prix-evolutif', [
                'id_course' => $this->course->id,
                'type' => 'dossards',
                'valeur_debut' => '1',
                'valeur_fin' => '50',
                'tarif' => 30,
                'ordre' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['tarif' => 30]);

        $this->assertDatabaseHas('PrixEvolutif', [
            'id_course' => $this->course->id,
            'tarif' => 30,
        ]);
    }

    public function test_admin_can_get_current_tariff_without_palier()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/tarif-actuel");

        $response->assertStatus(200)
            ->assertJsonFragment(['tarif' => 35]);
    }

    public function test_admin_can_update_palier()
    {
        $palier = PrixEvolutif::create([
            'id_course' => $this->course->id,
            'type' => 'dossards',
            'valeur_debut' => '1',
            'valeur_fin' => '50',
            'tarif' => 30,
            'ordre' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/organisateur/prix-evolutif/{$palier->id}", [
                'tarif' => 32,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['tarif' => 32]);
    }

    public function test_admin_can_delete_palier()
    {
        $palier = PrixEvolutif::create([
            'id_course' => $this->course->id,
            'type' => 'dossards',
            'valeur_debut' => '1',
            'valeur_fin' => '50',
            'tarif' => 30,
            'ordre' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/organisateur/prix-evolutif/{$palier->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Palier supprimé.']);
    }
}