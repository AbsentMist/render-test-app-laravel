<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Participant;
use App\Models\Inscription;
use Illuminate\Support\Facades\DB;

class InscriptionControllerTest extends TestCase
{
    // Relance la base de données à chaque test
    use DatabaseTransactions; 

    protected $user;   
    protected $participantId; 
    protected $courseId;

    protected function setUp(): void
    {
        parent::setUp();

        //Création d'un participant
        $this->user = User::factory()->create();
        
        $participant = Participant::factory()->create([
            'id_user' => $this->user->id,
        ]);
        
        $this->participantId = $participant->id;

        //Création d'un évènement
        $evenementId = DB::table('Evenement')->insertGetId([
            'nom' => 'Course Test',
            'site' => 'https://test.ch',
            'couleur_primaire' => '#000',
            'couleur_secondaire' => '#FFF',
            'is_actif' => 1
        ]);

        //Création d'une course
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

    public function test_un_participant_peut_creer_une_inscription()
    {
        $payload = [
            'id_course' => $this->courseId,
        ];

        //Récupère l'user créer dans le setUp()
        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Vérifie de la réponse HTTP 
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'tarif', 'status_paiement']);

        // Vérifie si l'inscription est bien en base de données
        $this->assertDatabaseHas('Inscription', [
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'Validé',
            'tarif' => 35,
        ]);
    }

    public function test_erreur_si_avertissement_obligatoire_non_coche()
    {
        // On modifie la course pour rendre l'avertissement obligatoire
        DB::table('Course')->where('id', $this->courseId)->update(['is_avertissement' => 1]);

        $payload = [
            'id_course' => $this->courseId,
            'avertissement_valide' => false // Oubli intentionnel
        ];

        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Vérification de l'erreur (422 Unprocessable Entity)
        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'message' => 'Vous devez accepter les conditions/avertissements liés à cette course pour vous inscrire.'
                 ]);
    }

    public function test_un_participant_ne_peut_pas_sinscrire_en_double()
    {
        //Inscription manuelle du participant une première fois
        DB::table('Inscription')->insert([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'En attente',
            'tarif' => 35
        ]);

        //On tente de le réinscrire à la même course
        $payload = [
            'id_course' => $this->courseId,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Vérification du blocage
        $response->assertStatus(409)
                 ->assertJsonFragment([
                     'message' => 'Vous êtes déjà inscrit à cette course (ou votre inscription est en attente de paiement).'
                 ]);
    }

    public function test_un_participant_peut_annuler_son_inscription_en_attente()
    {
        // Création d'une inscription en attente
        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'En attente',
            'tarif' => 35
        ]);

        // Requête DELETE
        $response = $this->actingAs($this->user)->deleteJson("/api/participant/inscriptions/{$inscriptionId}");

        $response->assertStatus(200);

        // On vérifie que le statut a bien changé
        $this->assertDatabaseHas('Inscription', [
            'id' => $inscriptionId,
            'status_paiement' => 'Annulé',
        ]);
    }

    public function test_un_participant_ne_peut_pas_annuler_une_inscription_payee()
    {
        // Création d'une inscription déjà payée
        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'Validé',
            'tarif' => 35
        ]);

        // Requête DELETE
        $response = $this->actingAs($this->user)->deleteJson("/api/participant/inscriptions/{$inscriptionId}");

        // Doit renvoyer une erreur 400 (Bad Request)
        $response->assertStatus(400)
                 ->assertJsonFragment([
                     'message' => 'Impossible d\'annuler une inscription déjà payée. Veuillez contacter l\'organisateur pour toute demande d\'annulation ou de remboursement.'
                 ]);
    }

    public function test_un_participant_peut_se_reinscrire_apres_une_annulation()
    {
        // Création d'une inscription Annulée
        $inscriptionId = DB::table('Inscription')->insertGetId([
            'id_participant' => $this->participantId,
            'id_course' => $this->courseId,
            'status_paiement' => 'Annulé',
            'tarif' => 35
        ]);

        // Il tente de se réinscrire (POST)
        $payload = [
            'id_course' => $this->courseId,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/participant/inscriptions', $payload);

        // Vérification : Code 200 (Mise à jour pour réinscription)
        $response->assertStatus(200);

        //Vérifie que la ligne existante est repassée "En attente"
        $this->assertDatabaseHas('Inscription', [
            'id' => $inscriptionId,
            'status_paiement' => 'Validé',
        ]);
    }
}