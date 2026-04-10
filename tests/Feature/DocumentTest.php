<?php

namespace Tests\Feature;

use App\Enums\StatutParticipant;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use DatabaseTransactions;

    private int $courseId;

    protected function setUp(): void
    {
        parent::setUp();

        $evenementId = DB::table('Evenement')->insertGetId([
            'nom' => 'Evenement test documents',
            'site' => 'https://test.ch',
            'couleur_primaire' => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif' => 1,
        ]);

        $this->courseId = DB::table('Course')->insertGetId([
            'id_evenement' => $evenementId,
            'nom' => 'Course test documents',
            'date_debut' => now()->addDays(30)->toDateString(),
            'date_fin' => now()->addDays(30)->toDateString(),
            'debut_inscription' => now()->subDays(10)->toDateString(),
            'fin_inscription' => now()->addDays(10)->toDateString(),
            'tarif' => 20,
            'status' => 'Ouvert',
            'type' => 'Route',
            'max_inscription' => 100,
            'premier_dossard' => 1,
            'dernier_dossard' => 500,
            'age_minimum' => 16,
            'is_avertissement' => 0,
        ]);
    }

    public function test_group_founder_can_upload_document_for_another_member_inscription(): void
    {
        Storage::fake('documents');

        $founderUser = User::factory()->create();
        $founderParticipant = Participant::factory()->create(['id_user' => $founderUser->id]);

        $memberUser = User::factory()->create();
        $memberParticipant = Participant::factory()->create(['id_user' => $memberUser->id]);

        $groupeId = DB::table('Groupe')->insertGetId([
            'nom' => 'Equipe Documents',
            'type' => 'Groupe',
            'code_entreprise' => null,
            'id_course' => $this->courseId,
        ]);

        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $groupeId,
            'id_participant' => $founderParticipant->id,
            'statut' => StatutParticipant::FONDATEUR->value,
        ]);

        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $groupeId,
            'id_participant' => $memberParticipant->id,
            'statut' => StatutParticipant::MEMBRE->value,
        ]);

        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $memberParticipant->id,
            'id_course' => $this->courseId,
            'id_groupe' => $groupeId,
            'status_paiement' => 'En attente',
            'tarif' => 20,
        ]);

        $response = $this->actingAs($founderUser)->post(
            "/api/participant/inscriptions/{$inscriptionId}/documents",
            ['file' => UploadedFile::fake()->create('certificat.pdf', 200, 'application/pdf')]
        );

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'document' => ['id', 'id_inscription', 'id_participant', 'url']]);

        $this->assertDatabaseHas('Document', [
            'id_inscription' => $inscriptionId,
            'id_participant' => $memberParticipant->id,
        ]);

        Storage::disk('documents')->assertExists($response->json('document.url'));
    }

    public function test_non_founder_group_member_cannot_upload_document_for_another_member_inscription(): void
    {
        Storage::fake('documents');

        $founderUser = User::factory()->create();
        $founderParticipant = Participant::factory()->create(['id_user' => $founderUser->id]);

        $memberUser = User::factory()->create();
        $memberParticipant = Participant::factory()->create(['id_user' => $memberUser->id]);

        $targetUser = User::factory()->create();
        $targetParticipant = Participant::factory()->create(['id_user' => $targetUser->id]);

        $groupeId = DB::table('Groupe')->insertGetId([
            'nom' => 'Equipe Documents 2',
            'type' => 'Groupe',
            'code_entreprise' => null,
            'id_course' => $this->courseId,
        ]);

        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $groupeId,
            'id_participant' => $founderParticipant->id,
            'statut' => StatutParticipant::FONDATEUR->value,
        ]);

        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $groupeId,
            'id_participant' => $memberParticipant->id,
            'statut' => StatutParticipant::MEMBRE->value,
        ]);

        DB::table('GroupeParticipant')->insert([
            'id_groupe' => $groupeId,
            'id_participant' => $targetParticipant->id,
            'statut' => StatutParticipant::MEMBRE->value,
        ]);

        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $targetParticipant->id,
            'id_course' => $this->courseId,
            'id_groupe' => $groupeId,
            'status_paiement' => 'En attente',
            'tarif' => 20,
        ]);

        $response = $this->actingAs($memberUser)->post(
            "/api/participant/inscriptions/{$inscriptionId}/documents",
            ['file' => UploadedFile::fake()->create('certificat.pdf', 200, 'application/pdf')]
        );

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Accès non autorisé.']);

        $this->assertDatabaseMissing('Document', [
            'id_inscription' => $inscriptionId,
            'id_participant' => $targetParticipant->id,
        ]);
    }
}
