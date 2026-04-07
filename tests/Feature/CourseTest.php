<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\User;
use App\Models\Avertissement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $evenement;

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

        // Créer un évènement de base réutilisable dans tous les tests
        $this->evenement = Evenement::create([
            'nom'                => 'Course des Ponts 2025',
            'site'               => 'https://coursedesponts.ch',
            'couleur_primaire'   => '#ff0000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);
    }

    // ===== INDEX PARTICIPANT =====

    public function test_participant_can_get_active_courses_for_evenement()
    {
        Course::create([
            'id_evenement'    => $this->evenement->id,
            'nom'             => 'Course active',
            'is_actif'        => true,
            'tarif'           => 35,
            'date_debut'      => '2027-05-15',
            'date_fin'        => '2027-05-15',
            'debut_inscription' => '2027-01-01',
            'fin_inscription' => '2027-05-10',
            'status'          => 'Ouvert',
            'type'            => 'Route',
            'max_inscription' => 500,
            'premier_dossard' => 1,
            'dernier_dossard' => 500,
            'age_minimum'     => 16,
        ]);
        
        // Ne doit pas apparaitre car fermé
        Course::create([
            'id_evenement'    => $this->evenement->id,
            'nom'             => 'Course inactive',
            'is_actif'        => false,
            'tarif'           => 35,
            'date_debut'      => '2025-05-15',
            'date_fin'        => '2025-05-15',
            'debut_inscription' => '2025-01-01',
            'fin_inscription' => '2025-05-10',
            'status'          => 'Fermé',
            'type'            => 'Route',
            'max_inscription' => 500,
            'premier_dossard' => 1,
            'dernier_dossard' => 500,
            'age_minimum'     => 16,
        ]);

        $response = $this->actingAs($this->admin)
                 ->getJson("/api/participant/evenements/{$this->evenement->id}/courses");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom_course' => 'Course active'])
                 ->assertJsonMissing(['nom_course' => 'Course inactive']);
    }

    public function test_participant_gets_404_for_unknown_evenement()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/participant/courses/999');

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Course introuvable.']);
    }

    // ===== INDEX ADMIN =====

    public function test_admin_can_get_all_courses_for_evenement()
    {
        Course::create([
            'id_evenement'      => $this->evenement->id,
            'nom'               => 'Course admin',
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
                         ->getJson("/api/organisateur/evenements/{$this->evenement->id}/courses");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Course admin']);
    }

    public function test_admin_gets_404_for_unknown_evenement()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/evenements/999/courses');

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Événement introuvable.']);
    }

    public function test_non_admin_cannot_get_courses()
    {
        $response = $this->getJson("/api/organisateur/evenements/{$this->evenement->id}/courses");

        $response->assertStatus(401);
    }

    // ===== SHOW =====

    public function test_admin_can_get_single_course()
    {
        $course = Course::create([
            'id_evenement'      => $this->evenement->id,
            'nom'               => '10km des Ponts',
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
                         ->getJson("/api/organisateur/courses/{$course->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => '10km des Ponts']);
    }

    public function test_show_course_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/organisateur/courses/999');

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Course introuvable.']);
    }

    // ===== STORE =====

    public function test_admin_can_create_course()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/courses', [
                             'id_evenement'      => $this->evenement->id,
                             'nom'               => 'Nouvelle course',
                             'tarif'             => 40,
                             'date_debut'        => '2025-06-01',
                             'date_fin'          => '2025-06-01',
                             'debut_inscription' => '2025-01-01',
                             'fin_inscription'   => '2025-05-30',
                             'status'            => 'Ouvert',
                             'type'              => 'Route',
                             'max_inscription'   => 300,
                             'premier_dossard'   => 1,
                             'dernier_dossard'   => 300,
                             'age_minimum'       => 18,
                             'is_actif'          => true,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'Nouvelle course']);

        $this->assertDatabaseHas('Course', ['nom' => 'Nouvelle course']);
    }

    public function test_create_course_fails_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/organisateur/courses', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'id_evenement', 'nom', 'tarif', 'date_debut',
                     'date_fin', 'status', 'type', 'max_inscription',
                     'premier_dossard', 'dernier_dossard', 'age_minimum',
                 ]);
    }

    // ===== UPDATE =====

    public function test_admin_can_update_course()
    {
        $course = Course::create([
            'id_evenement'      => $this->evenement->id,
            'nom'               => 'Ancien nom',
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
                         ->putJson("/api/organisateur/courses/{$course->id}", [
                             'nom' => 'Nouveau nom',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'Nouveau nom']);

        $this->assertDatabaseHas('Course', ['nom' => 'Nouveau nom']);
    }

    public function test_update_course_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson('/api/organisateur/courses/999', ['nom' => 'Test']);

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Course introuvable.']);
    }

    // ===== DESTROY =====

    public function test_admin_can_delete_course()
    {
        $course = Course::create([
            'id_evenement'      => $this->evenement->id,
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
                         ->deleteJson("/api/organisateur/courses/{$course->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Course supprimée avec succès.']);

        $this->assertDatabaseMissing('Course', ['id' => $course->id]);
    }

    public function test_delete_course_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->admin)
                         ->deleteJson('/api/organisateur/courses/999');

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Course introuvable.']);
    }

    // ===== AVERTISSEMENT DANS COURSE =====

    public function test_participant_gets_avertissement_within_course()
    {
        $avertissement = Avertissement::create([
            'titre'   => 'Verglas',
            'contenu' => 'Attention au verglas.',
            'modele'  => false,
        ]);

        Course::create([
            'id_evenement'      => $this->evenement->id,
            'nom'               => 'Course avec avertissement',
            'is_actif'          => true,
            'id_avertissement'  => $avertissement->id,
            'tarif'             => 35,
            'date_debut'        => '2027-05-15',
            'date_fin'          => '2027-05-15',
            'debut_inscription' => '2027-01-01',
            'fin_inscription'   => '2027-05-10',
            'status'            => 'Ouvert',
            'type'              => 'Route',
            'max_inscription'   => 500,
            'premier_dossard'   => 1,
            'dernier_dossard'   => 500,
            'age_minimum'       => 16,
        ]);

        $response = $this->actingAs($this->admin)
                ->getJson("/api/participant/evenements/{$this->evenement->id}/courses");

        $response->assertStatus(200)
            ->assertJsonPath('courses.0.avertissement.contenu', 'Attention au verglas.');
    }

    public function test_participant_gets_course_without_avertissement()
    {
        Course::create([
            'id_evenement'      => $this->evenement->id,
            'nom'               => 'Course sans avertissement',
            'is_actif'          => true,
            'tarif'             => 35,
            'date_debut'        => '2027-05-15',
            'date_fin'          => '2027-05-15',
            'debut_inscription' => '2027-01-01',
            'fin_inscription'   => '2027-05-10',
            'status'            => 'Ouvert',
            'type'              => 'Route',
            'max_inscription'   => 500,
            'premier_dossard'   => 1,
            'dernier_dossard'   => 500,
            'age_minimum'       => 16,
        ]);

        $response = $this->actingAs($this->admin)
                ->getJson("/api/participant/evenements/{$this->evenement->id}/courses");

        $response->assertStatus(200)
            ->assertJsonFragment(['avertissement' => null]);
    }
}