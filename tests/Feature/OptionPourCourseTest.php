<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\OptionPourCourse;
use App\Models\Course;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OptionPourCourseTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $course;
    protected $option;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer l'admin
        $roleId = DB::table('Role')->insertGetId(['type' => 'Administrateur']);
        $userId = DB::table('User')->insertGetId([
            'email'    => 'admin@test.ch',
            'password' => Hash::make('password'),
        ]);
        DB::table('UserRole')->insert(['id_user' => $userId, 'id_role' => $roleId]);
        DB::table('Administrateur')->insert(['id_user' => $userId]);
        $this->admin = User::find($userId);

        // Créer les données de base réutilisables
        $evenement = Evenement::create([
            'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course Test',
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

        $this->option = Option::create([
            'nom'         => 'T-shirt',
            'tarif'       => 20,
            'type'        => 'Cochable',
            'description' => 'Un t-shirt',
            'modele'      => false,
        ]);
    }

    // ===== INDEX ADMIN =====

    public function test_admin_can_get_all_options_pour_course()
    {
        OptionPourCourse::create([
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/optionCourse');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_get_options_pour_course()
    {
        $response = $this->getJson('/api/organisateur/optionCourse');

        $response->assertStatus(401);
    }

    // ===== INDEX PARTICIPANT =====

    public function test_participant_can_get_options_for_a_course()
    {
        OptionPourCourse::create([
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/participant/options/{$this->course->id}");

        $response->assertStatus(200);
    }

    public function test_participant_gets_404_if_no_options_for_course()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson("/api/participant/options/{$this->course->id}");

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Aucune option disponible pour cette course.']);
    }

    // ===== SHOW =====

    public function test_admin_can_get_single_option_pour_course()
    {
        OptionPourCourse::create([
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/optionCourse/{$this->course->id}/{$this->option->id}");

        $response->assertStatus(200);
    }

    public function test_show_returns_404_if_association_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson("/api/organisateur/optionCourse/{$this->course->id}/999");

        $response->assertStatus(404);
    }

    // ===== STORE =====

    public function test_admin_can_associate_option_to_course()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/optionCourse', [
                             'id_course' => $this->course->id,
                             'id_option' => $this->option->id,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Option associée à la course avec succès.']);

        $this->assertDatabaseHas('OptionPourCourse', [
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);
    }

    public function test_cannot_associate_same_option_twice()
    {
        OptionPourCourse::create([
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);

        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/optionCourse', [
                             'id_course' => $this->course->id,
                             'id_option' => $this->option->id,
                         ]);

        $response->assertStatus(409)
                 ->assertJsonFragment(['message' => 'Cette option est déjà associée à cette course.']);
    }

    public function test_store_fails_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/optionCourse', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_course', 'id_option']);
    }

    // ===== DESTROY =====

    public function test_admin_can_delete_association()
    {
        OptionPourCourse::create([
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/optionCourse/{$this->course->id}/{$this->option->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Association option-course supprimée avec succès.']);

        $this->assertDatabaseMissing('OptionPourCourse', [
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);
    }

    public function test_delete_returns_404_if_association_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/optionCourse/{$this->course->id}/999");

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Association introuvable.']);
    }

    // ===== DESTROY BY COURSE =====

    public function test_admin_can_delete_all_options_for_a_course()
    {
        $option2 = Option::create([
            'nom'         => 'Repas',
            'tarif'       => 15,
            'type'        => 'Cochable',
            'description' => 'Un repas',
            'modele'      => false,
        ]);

        OptionPourCourse::create(['id_course' => $this->course->id, 'id_option' => $this->option->id]);
        OptionPourCourse::create(['id_course' => $this->course->id, 'id_option' => $option2->id]);

        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/optionCourse/{$this->course->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('OptionPourCourse', ['id_course' => $this->course->id]);
    }

    public function test_destroy_by_course_returns_zero_if_no_associations()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson("/api/organisateur/optionCourse/{$this->course->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => "0 association(s) supprimée(s) pour la course {$this->course->id}."]);
    }
}