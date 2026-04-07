<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $evenement = Evenement::create([
            'nom'                => 'Evenement question',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course question',
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

    public function test_admin_can_get_model_questions()
    {
        Question::create(['enonce' => 'Question modèle', 'modele' => true]);
        Question::create(['enonce' => 'Question non modèle', 'modele' => false]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/organisateur/questions');

        $response->assertStatus(200)
            ->assertJsonFragment(['enonce' => 'Question modèle'])
            ->assertJsonMissing(['enonce' => 'Question non modèle']);
    }

    public function test_participant_can_get_questions_for_course()
    {
        $question = Question::create(['enonce' => 'Question liée', 'modele' => false]);
        DB::table('CourseQuestion')->insert([
            'id_course' => $this->course->id,
            'id_question' => $question->id,
            'ordre' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/questions/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['enonce' => 'Question liée']);
    }

    public function test_participant_gets_404_if_no_questions_for_course()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/questions/{$this->course->id}");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Aucune question disponible pour cette course.']);
    }

    public function test_admin_can_create_question_and_attach_to_course()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/organisateur/questions', [
                'enonce' => 'Question créée',
                'modele' => true,
                'ids_courses' => [$this->course->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['enonce' => 'Question créée']);

        $this->assertDatabaseHas('Question', ['enonce' => 'Question créée']);
    }

    public function test_admin_can_update_question()
    {
        $question = Question::create(['enonce' => 'Ancienne question', 'modele' => true]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/organisateur/questions/{$question->id}", [
                'enonce' => 'Question mise à jour',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['enonce' => 'Question mise à jour']);
    }

    public function test_admin_can_delete_question()
    {
        $question = Question::create(['enonce' => 'Question à supprimer', 'modele' => true]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/organisateur/questions/{$question->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Question supprimée avec succès.']);
    }
}