<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Participant;
use App\Enums\StatutParticipant;
use Illuminate\Support\Facades\DB;

class GroupeControllerTest extends TestCase
{
    //Relance la base de données à chaque test
    use DatabaseTransactions; 

    protected $user;   
    protected $participantId; 

    
    protected function setUp(): void
    {
        parent::setUp();

        //Création d'un participant et l'utilisateur associé
        $this->user = User::factory()->create();
        
        $participant = Participant::factory()->create([
            'id_user' => $this->user->id,
        ]);
        
        
        $this->participantId = $participant->id;
    }

    public function test_un_participant_peut_creer_un_groupe_et_devient_fondateur()
    {
        
        $payload = [
            'nom' => 'Les Fusées de Genève',
            'type' => 'Groupe', // ou 'Entreprise'
            'code_entreprise' => null, //est automatiquement généré par le GroupeController
        ];
        
        // L'utilisateur $this->user a été généré dans le setUp()
        $response = $this->actingAs($this->user)->postJson('/api/participant/groupes', $payload);
        
        //Vérification de la réponse HTTP
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id', 
                     'nom', 
                     'type', 
                     'participants'
                 ]);
        
        $this->assertDatabaseHas('Groupe', [
            'nom' => 'Les Fusées de Genève',
            'type' => 'Groupe',
        ]);
        
        $groupeId = $response->json('id');

        //Vérifie si la table d'association a été correctement remplie
        $this->assertDatabaseHas('GroupeParticipant', [
            'id_groupe' => $groupeId,
            'id_participant' => $this->participantId,
            'statut' => StatutParticipant::FONDATEUR->value,
        ]);
    }

    public function test_un_participant_peut_inviter_un_autre_participant_existant()
    {
        
        // Création d'un groupe
        $groupeId = DB::table('Groupe')->insertGetId([
            'nom' => 'Les Aigles de Genève',
            'type' => 'Groupe',
        ]);

        // On l'attache en tant que fondateur
        DB::table('GroupeParticipant')->insert([
            'id_groupe'      => $groupeId,
            'id_participant' => $this->participantId,
            'statut'         => StatutParticipant::FONDATEUR->value,
        ]);

        //Création d'un Participant aléatoire à ajouter au groupe
        $ami = Participant::factory()->create();
        
        $payload = [
            'id_participant' => $ami->id,
        ];

        // On fait la requête POST 
        $response = $this->actingAs($this->user)
                         ->postJson("/api/participant/groupes/{$groupeId}/participants", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'message' => 'Invitation envoyée (Participant ajouté en attente).',
                 ]);

        //Vérification table d'association remplie
        $this->assertDatabaseHas('GroupeParticipant', [
            'id_groupe'      => $groupeId,
            'id_participant' => $ami->id,
            'statut'         => StatutParticipant::EN_ATTENTE->value,
        ]);
    }

    public function test_creation_groupe_entreprise_genere_code_automatiquement()
    {
        $payload = [
            'nom'  => 'Rolex SA',
            'type' => 'Entreprise',
        ];

        //POST
        $response = $this->actingAs($this->user)
                         ->postJson('/api/participant/groupes', $payload);

        //Vérifier que la réponse est un succès
        $response->assertStatus(201);

        
        $codeGenere = $response->json('code_entreprise');

        //Vérification que le code est généré
        $this->assertNotNull($codeGenere);

        //Vérification du format du code
        $this->assertMatchesRegularExpression('/^E-[A-Z0-9]{7}$/', $codeGenere);

        //Vérification que le groupe a été créé avec le code généré
        $this->assertDatabaseHas('Groupe', [
            'nom'             => 'Rolex SA',
            'type'            => 'Entreprise',
            'code_entreprise' => $codeGenere,
        ]);
    }
}