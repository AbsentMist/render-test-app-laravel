<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Dossard;
use App\Models\Evenement;
use App\Models\Inscription;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EchangeDossardControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_initier_echange_retourne_une_reponse_json_sans_erreur_utf8(): void
    {
        [$userA, $participantA] = $this->createUserAndParticipant('alice@example.com', "Alice", "Dupont", "\xFF\xFE\xFD");
        [$userB, $participantB] = $this->createUserAndParticipant('bob@example.com', "Bob", "Martin", "\xFF\xFE\xFC");

        $course = $this->createCourse();
        $inscriptionA = $this->createValidatedInscriptionWithDossard($participantA->id, $course->id, 1);

        $response = $this->actingAs($userA)
            ->postJson('/api/participant/echange-dossard/initier', [
                'id_inscription' => $inscriptionA->id,
                'email_destinataire' => $userB->email,
            ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'message' => "Demande d'échange envoyée avec succès.",
        ]);
    }

    public function test_mes_demandes_recues_retourne_la_notification_taggee(): void
    {
        [$userA, $participantA] = $this->createUserAndParticipant('alice2@example.com', "Alice", "Dupont", "\xFF\xFE\xFB");
        [$userB, $participantB] = $this->createUserAndParticipant('bob2@example.com', "Bob", "Martin", "\xFF\xFE\xFA");

        $course = $this->createCourse();
        $inscriptionA = $this->createValidatedInscriptionWithDossard($participantA->id, $course->id, 2);
        $this->createPendingExchange($participantB->id, $course->id, $inscriptionA->id);

        $response = $this->actingAs($userB)
            ->getJson('/api/participant/echange-dossard/mes-demandes-recues');

        $response->assertOk();
        $response->assertJsonFragment([
            'tag' => 'Demande échange dossard',
        ]);
    }

    public function test_accepter_echange_valide_le_flux_et_ne_declenche_pas_une_erreur_500(): void
    {
        [$userA, $participantA] = $this->createUserAndParticipant('alice3@example.com', "Alice", "Dupont", "\xFF\xFE\xF9");
        [$userB, $participantB] = $this->createUserAndParticipant('bob3@example.com', "Bob", "Martin", "\xFF\xFE\xF8");

        $course = $this->createCourse();
        $inscriptionA = $this->createValidatedInscriptionWithDossard($participantA->id, $course->id, 3);
        $inscriptionB = $this->createPendingExchange($participantB->id, $course->id, $inscriptionA->id);

        $response = $this->actingAs($userB)
            ->postJson('/api/participant/echange-dossard/' . $inscriptionB->id . '/accepter');

        $response->assertOk();
        $response->assertJsonFragment([
            'message' => 'Échange accepté avec succès.',
        ]);

        $this->assertDatabaseHas('Inscription', [
            'id' => $inscriptionB->id,
            'status_paiement' => 'Validé',
        ]);

        $this->assertDatabaseHas('Inscription', [
            'id' => $inscriptionA->id,
            'status_paiement' => 'Validé',
        ]);
    }

    public function test_refuser_echange_cree_une_notification_info_et_un_mail(): void
    {
        [$userA, $participantA] = $this->createUserAndParticipant('alice4@example.com', "Alice", "Dupont", "\xFF\xFE\xF7");
        [$userB, $participantB] = $this->createUserAndParticipant('bob4@example.com', "Bob", "Martin", "\xFF\xFE\xF6");

        $course = $this->createCourse();
        $inscriptionA = $this->createValidatedInscriptionWithDossard($participantA->id, $course->id, 4);
        $inscriptionB = $this->createPendingExchange($participantB->id, $course->id, $inscriptionA->id);

        $response = $this->actingAs($userB)
            ->postJson('/api/participant/echange-dossard/' . $inscriptionB->id . '/refuser');

        $response->assertOk();

        $this->assertDatabaseMissing('Inscription', [
            'id' => $inscriptionB->id,
        ]);

        $message = DB::table('messages')->orderByDesc('id')->first();
        $payload = json_decode($message->content, true);
        $this->assertSame('exchange_refused', $payload['type']);
        $this->assertSame($userA->id, $payload['recipient_user_id']);
        $this->assertSame($userB->id, $payload['sender_user_id']);
    }

    private function createUserAndParticipant(string $email, string $prenom, string $nom, string $photo): array
    {
        $user = User::create([
            'email' => $email,
            'password' => bcrypt('password'),
        ]);

        $participant = Participant::create([
            'id_user' => $user->id,
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => '1990-01-01',
            'adresse' => 'Rue Test 1',
            'code_postal' => '1000',
            'ville' => 'Lausanne',
            'pays' => 'Suisse',
            'telephone' => '+41790000000' . random_int(10, 99),
            'nationalite' => 'Suisse',
            'taille_tshirt' => 'M',
            'sexe' => 'Homme',
            'photo' => $photo,
        ]);

        return [$user, $participant];
    }

    private function createCourse(): Course
    {
        $evenement = Evenement::create([
            'nom' => 'Run Test',
            'site' => 'https://example.com',
            'couleur_primaire' => '#111111',
            'couleur_secondaire' => '#eeeeee',
            'is_rabais' => false,
            'is_actif' => true,
            'is_interne' => false,
        ]);

        return Course::create([
            'id_evenement' => $evenement->id,
            'id_categorie' => null,
            'id_sous_categorie' => null,
            'id_avertissement' => null,
            'nom' => '10 km',
            'date_debut' => '2026-05-01',
            'date_fin' => '2026-05-01',
            'debut_inscription' => '2026-01-01',
            'fin_inscription' => '2099-01-01',
            'tarif' => 35,
            'status' => 'Publié',
            'type' => 'Individuel',
            'is_challenge' => false,
            'is_avertissement' => false,
            'is_document' => false,
            'is_questionnaire' => false,
            'is_dossard' => true,
            'is_actif' => true,
            'max_inscription' => 100,
            'max_nb_personne' => null,
            'premier_dossard' => 1,
            'dernier_dossard' => 100,
            'distance' => 10,
            'age_minimum' => 16,
            'age_maximum' => null,
        ]);
    }

    private function createValidatedInscriptionWithDossard(int $participantId, int $courseId, int $numero): Inscription
    {
        $inscription = Inscription::create([
            'id_participant' => $participantId,
            'id_course' => $courseId,
            'tarif' => 35,
            'status_paiement' => 'Validé',
            'montant_rabais' => 0,
            'avertissement_valide' => 1,
            'date_paiement' => now(),
        ]);

        Dossard::create([
            'numero' => $numero,
            'nom_personnalise' => null,
            'retrait_dossard' => false,
            'id_inscription' => $inscription->id,
        ]);

        return $inscription;
    }

    private function createPendingExchange(int $participantId, int $courseId, int $ancienneInscriptionId): Inscription
    {
        return Inscription::create([
            'id_participant' => $participantId,
            'id_course' => $courseId,
            'id_ancienne_inscription' => $ancienneInscriptionId,
            'tarif' => 0,
            'status_paiement' => 'En attente',
            'montant_rabais' => 0,
            'avertissement_valide' => 1,
            'date_paiement' => now(),
        ]);
    }
}