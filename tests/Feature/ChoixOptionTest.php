<?php

namespace Tests\Feature;

use App\Models\ChoixOption;
use App\Models\Course;
use App\Models\Evenement;
use App\Models\Inscription;
use App\Models\Option;
use App\Models\OptionPourCourse;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChoixOptionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected User $admin;
    protected Participant $participant;
    protected Course $course;
    protected Option $option;
    protected Inscription $inscription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->participant = Participant::factory()->create(['id_user' => $this->user->id]);
        $this->admin = $this->createAdminUser();

        $evenement = Evenement::create([
            'nom'                => 'Evenement choix option',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course choix option',
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

        $this->option = Option::create([
            'nom' => 'T-shirt',
            'tarif' => 20,
            'type' => 'Cochable',
            'description' => 'Un t-shirt',
            'modele' => false,
        ]);

        OptionPourCourse::create([
            'id_course' => $this->course->id,
            'id_option' => $this->option->id,
        ]);

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

    public function test_index_returns_choices_for_inscription()
    {
        ChoixOption::create([
            'id_inscription' => $this->inscription->id,
            'id_option' => $this->option->id,
            'quantite' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/participant/inscriptions/{$this->inscription->id}/choix-options");

        $response->assertStatus(200)
            ->assertJsonFragment(['quantite' => 1]);
    }

    public function test_admin_can_get_choices_for_option()
    {
        ChoixOption::create([
            'id_inscription' => $this->inscription->id,
            'id_option' => $this->option->id,
            'quantite' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/organisateur/options/{$this->option->id}/choix");

        $response->assertStatus(200)
            ->assertJsonFragment(['quantite' => 1]);
    }

    public function test_participant_can_store_choice_option()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/participant/choix-options', [
                'choix' => [
                    [
                        'id_inscription' => $this->inscription->id,
                        'id_option' => $this->option->id,
                        'quantite' => 2,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['quantite' => 2]);
    }

    public function test_participant_can_update_and_delete_choice_option()
    {
        ChoixOption::create([
            'id_inscription' => $this->inscription->id,
            'id_option' => $this->option->id,
            'quantite' => 1,
        ]);

        $update = $this->actingAs($this->user)
            ->putJson("/api/participant/choix-options/{$this->inscription->id}/{$this->option->id}", [
                'quantite' => 3,
            ]);

        $update->assertStatus(200)
            ->assertJsonFragment(['quantite' => 3]);

        $delete = $this->actingAs($this->user)
            ->deleteJson("/api/participant/choix-options/{$this->inscription->id}/{$this->option->id}");

        $delete->assertStatus(200)
            ->assertJsonFragment(['message' => 'Choix supprimé avec succès.']);
    }
}
