<?php

namespace Tests\Feature;

use App\Models\Groupe;
use App\Models\Participant;
use App\Models\User;
use App\Enums\StatutParticipant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GroupeInvitationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Participant $participant;
    protected Groupe $groupe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->participant = Participant::factory()->create(['id_user' => $this->user->id]);

        $this->groupe = Groupe::create(['nom' => 'Equipe Test', 'type' => 'Groupe']);
        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $this->groupe->id,
            'id_participant' => $this->participant->id,
            'statut' => StatutParticipant::FONDATEUR->value,
        ]);
    }

    // Accepter une invitation change le statut en ACCEPTE
    public function test_participant_peut_accepter_une_invitation()
    {
        $invite = Participant::factory()->create();
        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $this->groupe->id,
            'id_participant' => $invite->id,
            'statut' => StatutParticipant::EN_ATTENTE->value,
        ]);

        $inviteUser = User::factory()->create();
        DB::table('Participant')->where('id', $invite->id)->update(['id_user' => $inviteUser->id]);

        $response = $this->actingAs($inviteUser)
            ->postJson("/api/participant/groupes/{$this->groupe->id}/accepter");

        $response->assertStatus(200);
        $this->assertDatabaseHas('GroupeParticipant', [
            'id_groupe' => $this->groupe->id,
            'id_participant' => $invite->id,
            'statut' => 'Membre',
        ]);
    }

    // Refuser une invitation change le statut en REFUSE
    public function test_participant_peut_refuser_une_invitation()
    {
        $invite = Participant::factory()->create();
        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $this->groupe->id,
            'id_participant' => $invite->id,
            'statut' => StatutParticipant::EN_ATTENTE->value,
        ]);

        $inviteUser = User::factory()->create();
        DB::table('Participant')->where('id', $invite->id)->update(['id_user' => $inviteUser->id]);

        $response = $this->actingAs($inviteUser)
            ->postJson("/api/participant/groupes/{$this->groupe->id}/refuser");

        $response->assertStatus(200);
       $this->assertDatabaseMissing('GroupeParticipant', [
    'id_groupe'      => $this->groupe->id,
    'id_participant' => $invite->id,
        ]);
    }

    // Recuperer ses invitations en attente
    public function test_participant_peut_voir_ses_invitations()
    {
        $autreGroupe = Groupe::create(['nom' => 'Autre Equipe', 'type' => 'Groupe']);
        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $autreGroupe->id,
            'id_participant' => $this->participant->id,
            'statut' => StatutParticipant::EN_ATTENTE->value,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/participant/groupes/mes-invitations');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, count($data));
    }
}
