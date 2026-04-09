<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Evenement;
use App\Models\Inscription;
use App\Models\Participant;
use App\Models\PrixEvolutif;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrixEvolutifTarifActuelTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();

        $evenement = Evenement::create([
            'nom'                => 'Evenement tarif actuel',
            'site'               => 'https://test.ch',
            'couleur_primaire'   => '#000000',
            'couleur_secondaire' => '#ffffff',
            'is_actif'           => true,
        ]);

        $this->course = Course::create([
            'id_evenement'      => $evenement->id,
            'nom'               => 'Course tarif actuel',
            'date_debut'        => '2027-05-15',
            'date_fin'          => '2027-05-15',
            'debut_inscription' => '2027-01-01',
            'fin_inscription'   => '2027-05-10',
            'tarif'             => 35,
            'status'            => 'Ouvert',
            'type'              => 'Individuel',
            'max_inscription'   => 300,
            'premier_dossard'   => 1,
            'dernier_dossard'   => 300,
            'age_minimum'       => 16,
            'is_actif'          => true,
            'is_prix_evolutif'  => true,
        ]);

        // Paliers : 1->1 (30.-), 2->2 (40.-), 3->300 (50.-)
        PrixEvolutif::create(['id_course' => $this->course->id, 'type' => 'dossards', 'valeur_debut' => '1', 'valeur_fin' => '1', 'tarif' => 30, 'ordre' => 1]);
        PrixEvolutif::create(['id_course' => $this->course->id, 'type' => 'dossards', 'valeur_debut' => '2', 'valeur_fin' => '2', 'tarif' => 40, 'ordre' => 2]);
        PrixEvolutif::create(['id_course' => $this->course->id, 'type' => 'dossards', 'valeur_debut' => '3', 'valeur_fin' => '300', 'tarif' => 50, 'ordre' => 3]);
    }

    protected function createAdminUser(): User
    {
        $roleId = DB::table('Role')->where('type', 'Administrateur')->value('id');
        if (!$roleId) $roleId = DB::table('Role')->insertGetId(['type' => 'Administrateur']);
        $userId = DB::table('User')->insertGetId(['email' => 'admin_' . uniqid() . '@test.ch', 'password' => Hash::make('password')]);
        DB::table('UserRole')->insert(['id_user' => $userId, 'id_role' => $roleId]);
        DB::table('Administrateur')->insert(['id_user' => $userId]);
        return User::findOrFail($userId);
    }

    protected function createInscriptionValide(): void
    {
        $user = User::factory()->create();
        $participant = Participant::factory()->create(['id_user' => $user->id]);
        Inscription::create([
            'id_participant' => $participant->id,
            'id_course' => $this->course->id,
            'tarif' => 30,
            'status_paiement' => 'Validé',
            'montant_rabais' => 0,
            'avertissement_valide' => true,
        ]);
    }

    // 0 inscrits -> prochain dossard = 1 -> palier 1->1 -> 30.-
    public function test_tarif_actuel_retourne_premier_palier_sans_inscrits()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/tarif-actuel");
        $response->assertStatus(200)->assertJsonFragment(['tarif' => 30]);
    }

    // 1 inscrit -> prochain dossard = 2 -> palier 2->2 -> 40.-
    public function test_tarif_actuel_retourne_deuxieme_palier_avec_un_inscrit()
    {
        $this->createInscriptionValide();
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/tarif-actuel");
        $response->assertStatus(200)->assertJsonFragment(['tarif' => 40]);
    }

    // 2 inscrits -> prochain dossard = 3 -> palier 3->300 -> 50.-
    public function test_tarif_actuel_retourne_troisieme_palier_avec_deux_inscrits()
    {
        $this->createInscriptionValide();
        $this->createInscriptionValide();
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/tarif-actuel");
        $response->assertStatus(200)->assertJsonFragment(['tarif' => 50]);
    }

    // Sans paliers -> retourne le tarif de base
    public function test_tarif_actuel_retourne_tarif_de_base_si_aucun_palier()
    {
        $evenement = Evenement::create(['nom' => 'Test', 'site' => 'https://test.ch', 'couleur_primaire' => '#000', 'couleur_secondaire' => '#fff', 'is_actif' => true]);
        $course = Course::create([
            'id_evenement' => $evenement->id, 'nom' => 'Sans palier',
            'date_debut' => '2027-05-15', 'date_fin' => '2027-05-15',
            'debut_inscription' => '2027-01-01', 'fin_inscription' => '2027-05-10',
            'tarif' => 99, 'status' => 'Ouvert', 'type' => 'Individuel',
            'max_inscription' => 100, 'premier_dossard' => 1, 'dernier_dossard' => 100,
            'age_minimum' => 16, 'is_actif' => true, 'is_prix_evolutif' => true,
        ]);
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$course->id}/tarif-actuel");
        $response->assertStatus(200)->assertJsonFragment(['tarif' => 99]);
    }

    // Les inscriptions annulees ne comptent pas
    public function test_tarif_actuel_ignore_les_inscriptions_annulees()
    {
        $user = User::factory()->create();
        $participant = Participant::factory()->create(['id_user' => $user->id]);
        Inscription::create([
            'id_participant' => $participant->id,
            'id_course' => $this->course->id,
            'tarif' => 30,
            'status_paiement' => 'Annulé',
            'montant_rabais' => 0,
            'avertissement_valide' => true,
        ]);
        // Avec 0 inscrits valides -> toujours 30.-
        $response = $this->actingAs($this->admin)
            ->getJson("/api/participant/courses/{$this->course->id}/tarif-actuel");
        $response->assertStatus(200)->assertJsonFragment(['tarif' => 30]);
    }
}
