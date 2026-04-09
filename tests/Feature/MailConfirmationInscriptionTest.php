<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationInscriptionMail;
use Tests\TestCase;

/**
 * Tests pour l'envoi du mail de confirmation lors d'une inscription (Tache 1.1)
 */
class MailConfirmationInscriptionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Participant $participant;
    protected int $courseId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->participant = Participant::factory()->create(['id_user' => $this->user->id]);

        $evenement = Evenement::create([
            'nom' => 'Evenement mail test', 'site' => 'https://test.ch',
            'couleur_primaire' => '#000000', 'couleur_secondaire' => '#ffffff', 'is_actif' => true,
        ]);

        $this->courseId = DB::table('Course')->insertGetId([
            'id_evenement' => $evenement->id,
            'nom' => 'Course mail test',
            'date_debut' => '2027-05-15', 'date_fin' => '2027-05-15',
            'debut_inscription' => '2027-01-01', 'fin_inscription' => '2027-05-10',
            'tarif' => 35, 'status' => 'Ouvert', 'type' => 'Individuel',
            'max_inscription' => 100, 'premier_dossard' => 1, 'dernier_dossard' => 100,
            'age_minimum' => 16, 'is_avertissement' => 0, 'is_actif' => 1,
        ]);
    }

    // Le mail de confirmation est envoye apres une inscription reussie
    public function test_mail_confirmation_envoye_apres_inscription()
    {
        Mail::fake();

        $response = $this->actingAs($this->user)
            ->postJson('/api/participant/inscriptions', [
                'id_course' => $this->courseId,
            ]);

        $response->assertStatus(201);
        Mail::assertSent(ConfirmationInscriptionMail::class);
    }

    // Un seul mail est envoye (pas de doublons)
    public function test_mail_confirmation_envoye_une_seule_fois()
    {
        Mail::fake();

        $this->actingAs($this->user)
            ->postJson('/api/participant/inscriptions', [
                'id_course' => $this->courseId,
            ]);

        Mail::assertSent(ConfirmationInscriptionMail::class, 1);
    }

    // Le mail n'est pas envoye si l'inscription echoue (doublon)
    public function test_mail_confirmation_non_envoye_si_inscription_echoue()
    {
        Mail::fake();

        // Premiere inscription OK
        $this->actingAs($this->user)
            ->postJson('/api/participant/inscriptions', ['id_course' => $this->courseId]);

        Mail::assertSent(ConfirmationInscriptionMail::class, 1);

        // Deuxieme inscription -> doit echouer (409)
        $this->actingAs($this->user)
            ->postJson('/api/participant/inscriptions', ['id_course' => $this->courseId]);

        // Toujours 1 seul mail (pas de 2eme envoi)
        Mail::assertSent(ConfirmationInscriptionMail::class, 1);
    }
}
