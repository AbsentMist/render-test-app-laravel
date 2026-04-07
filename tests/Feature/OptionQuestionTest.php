<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\OptionQuestion;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OptionQuestionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $evenement = Evenement::create([
            'nom'                => 'Evenement option question',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course option question',
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

        $this->question = Question::create(['enonce' => 'Choisis', 'modele' => false]);
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

    public function test_index_returns_choices_for_question()
    {
        OptionQuestion::create(['id_question' => $this->question->id, 'texte_option' => 'Oui']);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/organisateur/questions/{$this->question->id}/choix");

        $response->assertStatus(200)
            ->assertJsonFragment(['texte_option' => 'Oui']);
    }

    public function test_show_choice_returns_single_option()
    {
        $option = OptionQuestion::create(['id_question' => $this->question->id, 'texte_option' => 'Oui']);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/organisateur/choix/{$option->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['texte_option' => 'Oui']);
    }

    public function test_admin_can_create_choice()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/organisateur/questions/{$this->question->id}/choix", [
                'texte_option' => 'Oui',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['texte_option' => 'Oui']);
    }

    public function test_admin_can_update_choice()
    {
        $option = OptionQuestion::create(['id_question' => $this->question->id, 'texte_option' => 'Oui']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/organisateur/choix/{$option->id}", [
                'texte_option' => 'Non',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['texte_option' => 'Non']);
    }

    public function test_admin_can_delete_choice()
    {
        $option = OptionQuestion::create(['id_question' => $this->question->id, 'texte_option' => 'Oui']);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/organisateur/choix/{$option->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Choix de réponse supprimé avec succès.']);
    }
}