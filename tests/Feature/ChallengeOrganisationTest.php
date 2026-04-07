<?php

namespace Tests\Feature;

use App\Models\ChallengeOrganisation;
use App\Models\Course;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChallengeOrganisationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $evenement = Evenement::create([
            'nom'                => 'Evenement challenge',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course challenge',
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

    public function test_index_returns_organisations_for_course()
    {
        ChallengeOrganisation::create([
            'id_course' => $this->course->id,
            'nom' => 'Alpha',
            'type' => 'Groupe',
        ]);
        ChallengeOrganisation::create([
            'id_course' => $this->course->id,
            'nom' => 'Beta',
            'type' => 'Entreprise',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/challenge-organisations");

        $response->assertStatus(200)
            ->assertJsonFragment(['nom' => 'Alpha'])
            ->assertJsonFragment(['nom' => 'Beta']);
    }

    public function test_admin_can_create_challenge_organisation()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/organisateur/challenge-organisations', [
                'id_course' => $this->course->id,
                'nom' => 'Alpha',
                'type' => 'Groupe',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['nom' => 'Alpha']);

        $this->assertDatabaseHas('ChallengeOrganisation', [
            'id_course' => $this->course->id,
            'nom' => 'Alpha',
            'type' => 'Groupe',
        ]);
    }

    public function test_create_returns_409_if_duplicate_exists()
    {
        ChallengeOrganisation::create([
            'id_course' => $this->course->id,
            'nom' => 'Alpha',
            'type' => 'Groupe',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/organisateur/challenge-organisations', [
                'id_course' => $this->course->id,
                'nom' => 'Alpha',
                'type' => 'Groupe',
            ]);

        $response->assertStatus(409);
    }

    public function test_admin_can_delete_challenge_organisation()
    {
        $organisation = ChallengeOrganisation::create([
            'id_course' => $this->course->id,
            'nom' => 'Alpha',
            'type' => 'Groupe',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/organisateur/challenge-organisations/{$organisation->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Organisation supprimée.']);

        $this->assertDatabaseMissing('ChallengeOrganisation', ['id' => $organisation->id]);
    }
}