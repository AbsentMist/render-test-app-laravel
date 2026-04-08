<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Participant;
use App\Models\Inscription;
use Illuminate\Support\Facades\DB;

class InscriptionTest extends TestCase
{
    // Roll back DB changes after each test
    use DatabaseTransactions; 

    protected $user;   
    protected $participantId; 
    protected $courseId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a participant
        $this->user = User::factory()->create();
        
        $participant = Participant::factory()->create([
            'id_user' => $this->user->id,
        ]);
        
        $this->participantId = $participant->id;

        // Create an event
        $evenementId = DB::table('Evenement')->insertGetId([
            'nom' => 'Course Test',
            'site' => 'https://test.ch',
            'couleur_primaire' => '#000',
            'couleur_secondaire' => '#FFF',
            'is_actif' => 1
        ]);

        // Create a course
        $this->courseId = DB::table('Course')->insertGetId([
            'id_evenement' => $evenementId,
            'nom' => '10km de Test',
            'date_debut' => '2026-05-15',
            'date_fin' => '2026-05-15',
            'debut_inscription' => '2026-01-01',
            'fin_inscription' => '2026-05-10',
            'tarif' => 35,
            'status' => 'Ouvert',
            'type' => 'Route',
            'max_inscription' => 100,
            'premier_dossard' => 1,
            'dernier_dossard' => 100,
            'age_minimum' => 18,
            'is_avertissement' => 0
        ]);
    }

    public function test_participant_can_create_registration()
    {
        $payload = [
            'id_course' => $this->courseId,
        ];

        // Use the authenticated user created in setUp()
        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Validate HTTP response
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'tarif', 'status_paiement']);

        // Validate registration row in database
        $this->assertDatabaseHas('Inscription', [
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'Validé',
            'tarif' => 35,
        ]);
    }

    public function test_fails_when_required_warning_not_accepted()
    {
        // Make warning acceptance mandatory for this course
        DB::table('Course')->where('id', $this->courseId)->update(['is_avertissement' => 1]);

        $payload = [
            'id_course' => $this->courseId,
            'avertissement_valide' => false // Intentionally omitted
        ];

        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Validate expected validation error (422)
        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'message' => 'Vous devez accepter les conditions/avertissements liés à cette course pour vous inscrire.'
                 ]);
    }

    public function test_participant_cannot_register_twice()
    {
        // First manual registration for this participant
        DB::table('Inscription')->insert([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'En attente',
            'tarif' => 35
        ]);

        // Try registering the same participant again
        $payload = [
            'id_course' => $this->courseId,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Validate duplicate registration is blocked
        $response->assertStatus(409)
                 ->assertJsonFragment([
                     'message' => 'Vous êtes déjà inscrit à cette course (ou votre inscription est en attente de paiement).'
                 ]);
    }

    public function test_participant_can_cancel_pending_registration()
    {
        // Create a pending registration
        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'En attente',
            'tarif' => 35
        ]);

        // Send DELETE request
        $response = $this->actingAs($this->user)->deleteJson("/api/participant/inscriptions/{$inscriptionId}");

        $response->assertStatus(200);

        // Validate status update in database
        $this->assertDatabaseHas('Inscription', [
            'id' => $inscriptionId,
            'status_paiement' => 'Annulé',
        ]);
    }

    public function test_participant_cannot_cancel_paid_registration()
    {
        // Create a paid registration
        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'Validé',
            'tarif' => 35
        ]);

        // Send DELETE request
        $response = $this->actingAs($this->user)->deleteJson("/api/participant/inscriptions/{$inscriptionId}");

        // Should return 400 (Bad Request)
        $response->assertStatus(400)
                 ->assertJsonFragment([
                     'message' => 'Impossible d\'annuler une inscription déjà payée. Veuillez contacter l\'organisateur pour toute demande d\'annulation ou de remboursement.'
                 ]);
    }

    public function test_participant_can_reregister_after_cancellation()
    {
        // Create a canceled registration
        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'Annulé',
            'tarif' => 35
        ]);

        // Try to register again (POST)
        $payload = [
            'id_course' => $this->courseId,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Validate status code 200 (re-registration update)
        $response->assertStatus(200);

        // Validate existing row has been updated
        $this->assertDatabaseHas('Inscription', [
            'id' => $inscriptionId,
            'status_paiement' => 'Validé',
        ]);
    }
}