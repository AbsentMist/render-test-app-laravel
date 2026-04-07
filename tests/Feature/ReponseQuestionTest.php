<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\Inscription;
use App\Models\OptionQuestion;
use App\Models\Participant;
use App\Models\Question;
use App\Models\ReponseQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReponseQuestionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected User $admin;
    protected Participant $participant;
    protected Course $course;
    protected Question $question;
    protected OptionQuestion $optionQuestion;
    protected Inscription $inscription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->participant = Participant::factory()->create(['id_user' => $this->user->id]);
        $this->admin = $this->createAdminUser();

        $evenement = Evenement::create([
            'nom'                => 'Evenement reponse question',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course reponse question',
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

        $this->question = Question::create(['enonce' => 'Choisis une réponse', 'modele' => false]);
        $this->optionQuestion = OptionQuestion::create(['id_question' => $this->question->id, 'texte_option' => 'Oui']);

        $this->inscription = Inscription::create([
            'id_participant' => $this->participant->id,
            'id_course' => $this->course->id,
            'tarif' => 35,
            'status_paiement' => 'En attente',
            'montant_rabais' => 0,
            'avertissement_valide' => false,
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

    public function test_index_returns_responses_for_inscription()
    {
        ReponseQuestion::create([
            'id_inscription' => $this->inscription->id,
            'id_question' => $this->question->id,
            'id_option_choisie' => $this->optionQuestion->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/participant/inscriptions/{$this->inscription->id}/reponses");

        $response->assertStatus(200)
            ->assertJsonFragment(['id_question' => $this->question->id]);
    }

    public function test_admin_can_get_responses_for_question()
    {
        ReponseQuestion::create([
            'id_inscription' => $this->inscription->id,
            'id_question' => $this->question->id,
            'id_option_choisie' => $this->optionQuestion->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/organisateur/questions/{$this->question->id}/reponses");

        $response->assertStatus(200)
            ->assertJsonFragment(['id_inscription' => $this->inscription->id]);
    }

    public function test_participant_can_store_responses()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/participant/reponses-questions', [
                'reponses' => [
                    [
                        'id_inscription' => $this->inscription->id,
                        'id_question' => $this->question->id,
                        'id_option_choisie' => $this->optionQuestion->id,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['id_question' => $this->question->id]);
    }

    public function test_participant_can_delete_response()
    {
        ReponseQuestion::create([
            'id_inscription' => $this->inscription->id,
            'id_question' => $this->question->id,
            'id_option_choisie' => $this->optionQuestion->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/participant/reponses-questions/{$this->inscription->id}/{$this->question->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Réponse supprimée avec succès.']);
    }
}