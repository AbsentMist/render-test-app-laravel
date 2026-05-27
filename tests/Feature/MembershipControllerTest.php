<?php

namespace Tests\Feature;

use App\Mail\ApprobationMembershipMail;
use App\Models\FormulaireMembership;
use App\Models\InvitationMembership;
use App\Models\Message;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tests feature pour le controleur de membership.
 *
 * @author Steven Ngoie
 * @return void
 */
class MembershipControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureMembershipSchema();
        $this->ensureRoleExists('Participant');
        $this->ensureRoleExists('Administrateur');
        $this->ensureRoleExists('Membre');
    }

    public function test_acces_participant_membership_returns_false_without_active_invitation(): void
    {
        [$user] = $this->createUserWithParticipant('access_none_' . uniqid() . '@test.ch');

        $response = $this->actingAs($user)->getJson('/api/participant/membership/acces');

        $response->assertStatus(200)
            ->assertJson([
                'has_access' => false,
                'invitation' => null,
                'formulaire' => null,
            ]);
    }

    public function test_acces_participant_membership_returns_invitation_and_formulaire_when_active(): void
    {
        [$user] = $this->createUserWithParticipant('access_yes_' . uniqid() . '@test.ch');
        [$admin] = $this->createAdminUser('access_admin_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');
        $formulaire = $this->createFormulaire($invitation->id, ['status' => 'À compléter']);

        $response = $this->actingAs($user)->getJson('/api/participant/membership/acces');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $invitation->id])
            ->assertJsonFragment(['id' => $formulaire->id])
            ->assertJsonFragment(['has_access' => true]);
    }

    public function test_rechercher_participant_par_email_returns_participant_info(): void
    {
        [$admin] = $this->createAdminUser('search_admin_' . uniqid() . '@test.ch');
        [$targetUser, $participant] = $this->createUserWithParticipant('search_target_' . uniqid() . '@test.ch', [
            'prenom' => 'Alice',
            'nom' => 'Dupont',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/organisateur/membership/participants/rechercher?email=' . urlencode($targetUser->email));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id_user' => $targetUser->id,
                'email' => $targetUser->email,
                'prenom' => $participant->prenom,
                'nom' => $participant->nom,
            ]);
    }

    public function test_inviter_participant_creates_invitation_formulaire_and_notifications(): void
    {
        Mail::fake();

        [$admin] = $this->createAdminUser('invite_admin_' . uniqid() . '@test.ch');
        [$participantUser, $participant] = $this->createUserWithParticipant('invite_target_' . uniqid() . '@test.ch', [
            'prenom' => 'Alicia',
            'nom' => 'Meyer',
        ]);
        [$otherAdmin] = $this->createAdminUser('invite_other_' . uniqid() . '@test.ch');

        $response = $this->actingAs($admin)->postJson('/api/organisateur/membership/invitations', [
            'email' => $participantUser->email,
            'commentaire_admin' => 'Bienvenue dans le membership',
        ]);

        $response->assertStatus(201);

        $invitation = InvitationMembership::query()
            ->where('id_user_participant', $participantUser->id)
            ->latest('id')
            ->first();

        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->first();

        $this->assertNotNull($invitation);
        $this->assertSame('En cours', $invitation->status);
        $this->assertSame($admin->id, $invitation->id_admin_createur);
        $this->assertNotNull($formulaire);
        $this->assertSame('À compléter', $formulaire->status);
        $this->assertSame(25.00, (float) $formulaire->prix);
        $this->assertTrue($this->messageExists('membership_invitation_to_complete', $participantUser->id, $invitation->id));
        $this->assertTrue($this->messageExists('membership_invitation_info', $otherAdmin->id, $invitation->id));
    }

    public function test_soumettre_demande_finalizes_formulaire_and_notifies_admins(): void
    {
        [$admin] = $this->createAdminUser('submit_admin_' . uniqid() . '@test.ch');
        [$user, $participant] = $this->createUserWithParticipant('submit_target_' . uniqid() . '@test.ch', [
            'prenom' => 'Jean',
            'nom' => 'Dupont',
        ]);
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');

        $response = $this->actingAs($user)->postJson('/api/participant/membership/demander', $this->buildFormPayload());

        $response->assertStatus(201);

        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->first();

        $invitation->refresh();

        $this->assertNotNull($formulaire);
        $this->assertSame('En attente de validation', $formulaire->status);
        $this->assertSame($user->email, $formulaire->email);
        $this->assertSame('Complété', $invitation->status);
        $this->assertTrue($this->messageExists('new_membership_request', $admin->id, $formulaire->id));
    }

    public function test_ma_demande_returns_latest_formulaire_for_connected_user(): void
    {
        [$admin] = $this->createAdminUser('me_admin_' . uniqid() . '@test.ch');
        [$user] = $this->createUserWithParticipant('me_target_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');
        $formulaire = $this->createFormulaire($invitation->id, [
            'email' => $user->email,
            'status' => 'En attente de validation',
        ]);

        $response = $this->actingAs($user)->getJson('/api/participant/membership/ma-demande');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $formulaire->id]);
    }

    public function test_lister_demandes_returns_formulaires_for_admin(): void
    {
        [$admin] = $this->createAdminUser('list_admin_' . uniqid() . '@test.ch');
        [$user] = $this->createUserWithParticipant('list_target_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');
        $formulaire = $this->createFormulaire($invitation->id, [
            'email' => $user->email,
            'status' => 'En attente de validation',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/organisateur/membership/demandes');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $formulaire->id])
            ->assertJsonFragment(['id_invitation' => $invitation->id]);
    }

    public function test_lister_demandes_en_attente_returns_only_pending_validation_forms(): void
    {
        [$admin] = $this->createAdminUser('pending_admin_' . uniqid() . '@test.ch');
        [$userPending] = $this->createUserWithParticipant('pending_target_' . uniqid() . '@test.ch');
        [$userOther] = $this->createUserWithParticipant('other_target_' . uniqid() . '@test.ch');

        $invitationPending = $this->createInvitation($userPending->id, $admin->id, 'En cours');
        $this->createFormulaire($invitationPending->id, ['status' => 'En attente de validation']);

        $invitationOther = $this->createInvitation($userOther->id, $admin->id, 'En cours');
        $this->createFormulaire($invitationOther->id, ['status' => 'Approuvée']);

        $response = $this->actingAs($admin)->getJson('/api/organisateur/membership/demandes/en-attente');

        $response->assertStatus(200);
        $statuses = collect($response->json())->pluck('status')->unique()->values()->all();
        $this->assertSame(['En attente de validation'], $statuses);
    }

    public function test_approuver_demande_assigns_member_role_and_completes_invitation(): void
    {
        Mail::fake();

        [$admin] = $this->createAdminUser('approve_admin_' . uniqid() . '@test.ch');
        [$user] = $this->createUserWithParticipant('approve_target_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');
        $formulaire = $this->createFormulaire($invitation->id, [
            'email' => $user->email,
            'status' => 'En attente de validation',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/organisateur/membership/demandes/' . $formulaire->id . '/approuver');

        $response->assertStatus(200);

        $formulaire->refresh();
        $invitation->refresh();

        $this->assertSame('Approuvée', $formulaire->status);
        $this->assertSame('Complété', $invitation->status);
        $this->assertDatabaseHas('UserRole', [
            'id_user' => $user->id,
            'id_role' => $this->roleId('Membre'),
        ]);

        Mail::assertSent(ApprobationMembershipMail::class, function (ApprobationMembershipMail $mail) use ($formulaire) {
            return $mail->hasTo($formulaire->email);
        });
    }

    public function test_remettre_a_completer_demande_reopens_invitation_and_sets_notes(): void
    {
        [$admin] = $this->createAdminUser('complete_admin_' . uniqid() . '@test.ch');
        [$user] = $this->createUserWithParticipant('complete_target_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');
        $formulaire = $this->createFormulaire($invitation->id, [
            'email' => $user->email,
            'status' => 'En attente de validation',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/organisateur/membership/demandes/' . $formulaire->id . '/a-completer', [
            'commentaire_admin' => 'Merci de corriger votre adresse.',
        ]);

        $response->assertStatus(200);

        $formulaire->refresh();
        $invitation->refresh();

        $this->assertSame('À compléter', $formulaire->status);
        $this->assertSame('Merci de corriger votre adresse.', $formulaire->notes_admin);
        $this->assertSame('En cours', $invitation->status);
    }

    public function test_annuler_invitation_cancels_invitation_and_linked_formulaire(): void
    {
        [$admin] = $this->createAdminUser('cancel_admin_' . uniqid() . '@test.ch');
        [$user] = $this->createUserWithParticipant('cancel_target_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');
        $formulaire = $this->createFormulaire($invitation->id, [
            'email' => $user->email,
            'status' => 'À compléter',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/organisateur/membership/invitations/' . $invitation->id . '/annuler', [
            'commentaire_annulation' => 'Invitation annulée.',
        ]);

        $response->assertStatus(200);

        $formulaire->refresh();
        $invitation->refresh();

        $this->assertSame('Annulé', $invitation->status);
        $this->assertSame('Annulé', $formulaire->status);
        $this->assertSame('Invitation annulée.', $invitation->commentaire_annulation);
    }

    public function test_checkout_payment_finalizes_membership_and_assigns_member_role(): void
    {
        Mail::fake();

        [$admin] = $this->createAdminUser('checkout_admin_' . uniqid() . '@test.ch');
        [$user] = $this->createUserWithParticipant('checkout_target_' . uniqid() . '@test.ch');
        $invitation = $this->createInvitation($user->id, $admin->id, 'En cours');

        $payload = [
            'invitation_id' => $invitation->id,
            'prix' => 25,
            'formulaire' => $this->buildFormPayload(),
        ];

        $response = $this->actingAs($user)->postJson('/api/participant/membership/checkout', $payload);

        $response->assertStatus(201);

        $formulaire = FormulaireMembership::query()
            ->where('id_invitation', $invitation->id)
            ->first();

        $invitation->refresh();

        $this->assertNotNull($formulaire);
        $this->assertSame('Approuvée', $formulaire->status);
        $this->assertSame('25.00', (string) $formulaire->prix);
        $this->assertSame('Complété', $invitation->status);
        $this->assertDatabaseHas('UserRole', [
            'id_user' => $user->id,
            'id_role' => $this->roleId('Membre'),
        ]);
    }

    private function buildFormPayload(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'TestNom',
            'prenom' => 'TestPrenom',
            'adresse' => 'Rue de Test 1',
            'code_postal' => '1200',
            'ville' => 'Geneve',
            'pays' => 'Suisse',
            'telephone' => '079 123 45 67',
            'date_naissance' => '1990-01-01',
            'description' => 'Description suffisamment longue pour valider le formulaire.',
        ], $overrides);
    }

    private function createUserWithParticipant(string $email, array $participantOverrides = []): array
    {
        $user = User::create([
            'email' => $email,
            'password' => Hash::make('StrongPwd!123'),
        ]);

        $participant = Participant::factory()->create(array_merge([
            'id_user' => $user->id,
            'prenom' => 'Prenom',
            'nom' => 'Nom',
        ], $participantOverrides));

        DB::table('UserRole')->insertOrIgnore([
            'id_user' => $user->id,
            'id_role' => $this->roleId('Participant'),
        ]);

        return [$user, $participant];
    }

    private function createAdminUser(string $email, array $participantOverrides = []): array
    {
        [$user, $participant] = $this->createUserWithParticipant($email, array_merge([
            'prenom' => 'Admin',
            'nom' => 'User',
        ], $participantOverrides));

        DB::table('UserRole')->insertOrIgnore([
            'id_user' => $user->id,
            'id_role' => $this->roleId('Administrateur'),
        ]);

        return [$user, $participant];
    }

    private function createInvitation(int $participantUserId, int $adminUserId, string $status = 'En cours'): InvitationMembership
    {
        return InvitationMembership::create([
            'id_user_participant' => $participantUserId,
            'id_admin_createur' => $adminUserId,
            'commentaire_admin' => 'Bienvenue',
            'status' => $status,
            'date_invitation' => now(),
        ]);
    }

    private function createFormulaire(int $invitationId, array $overrides = []): FormulaireMembership
    {
        return FormulaireMembership::create(array_merge([
            'id_invitation' => $invitationId,
            'nom' => 'TestNom',
            'prenom' => 'TestPrenom',
            'email' => 'test@example.ch',
            'adresse' => 'Rue de Test 1',
            'code_postal' => '1200',
            'ville' => 'Geneve',
            'pays' => 'Suisse',
            'telephone' => '079 123 45 67',
            'date_naissance' => '1990-01-01',
            'description' => 'Description suffisamment longue pour valider le formulaire.',
            'status' => 'À compléter',
            'date_creation' => now(),
            'prix' => 25,
        ], $overrides));
    }

    private function ensureRoleExists(string $type): void
    {
        if (!Schema::hasTable('Role')) {
            Schema::create('Role', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
            });
        }

        if (!DB::table('Role')->where('type', $type)->exists()) {
            DB::table('Role')->insert(['type' => $type]);
        }
    }

    private function roleId(string $type): int
    {
        return (int) DB::table('Role')->where('type', $type)->value('id');
    }

    private function messageExists(string $type, int $recipientUserId, int $demandeId): bool
    {
        return Message::query()->get()->contains(function (Message $message) use ($type, $recipientUserId, $demandeId) {
            $payload = json_decode($message->content, true);

            return is_array($payload)
                && ($payload['type'] ?? null) === $type
                && ($payload['recipient_user_id'] ?? null) === $recipientUserId
                && (($payload['demande_id'] ?? null) === $demandeId || ($payload['invitation_id'] ?? null) === $demandeId);
        });
    }

    private function ensureMembershipSchema(): void
    {
        if (!Schema::hasTable('User')) {
            Schema::create('User', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('Participant')) {
            Schema::create('Participant', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_user');
                $table->string('nom', 100);
                $table->string('prenom', 100);
                $table->date('date_naissance')->nullable();
                $table->string('equipe_nom', 100)->nullable();
                $table->string('adresse', 100)->nullable();
                $table->string('code_postal', 10)->nullable();
                $table->string('ville', 100)->nullable();
                $table->string('pays', 100)->nullable();
                $table->string('telephone', 20)->nullable();
                $table->string('nationalite', 100)->nullable();
                $table->string('instagram', 255)->nullable();
                $table->string('facebook', 255)->nullable();
                $table->string('taille_tshirt', 10)->nullable();
                $table->string('sexe', 10)->nullable();
                $table->binary('photo')->nullable();
            });
        }

        if (!Schema::hasTable('Role')) {
            Schema::create('Role', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
            });
        }

        if (!Schema::hasTable('UserRole')) {
            Schema::create('UserRole', function (Blueprint $table) {
                $table->unsignedBigInteger('id_user');
                $table->unsignedBigInteger('id_role');
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->text('content');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('Membre')) {
            Schema::create('Membre', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_user')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('InvitationMembership')) {
            Schema::create('InvitationMembership', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_user_participant');
                $table->foreignId('id_admin_createur');
                $table->string('commentaire_admin', 500)->nullable();
                $table->string('status', 20)->default('En cours');
                $table->timestamp('date_invitation')->useCurrent();
                $table->timestamp('date_annulation')->nullable();
                $table->foreignId('id_admin_annulation')->nullable();
                $table->string('commentaire_annulation', 500)->nullable();
            });
        }

        if (!Schema::hasTable('FormulaireMembership')) {
            Schema::create('FormulaireMembership', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_invitation');
                $table->string('nom', 100);
                $table->string('prenom', 100);
                $table->string('email', 255);
                $table->string('adresse', 255);
                $table->string('code_postal', 10);
                $table->string('ville', 100);
                $table->string('pays', 100);
                $table->string('telephone', 20);
                $table->date('date_naissance');
                $table->text('description');
                $table->decimal('prix', 8, 2)->default(25.00);
                $table->string('status', 30)->default('À compléter');
                $table->timestamp('date_creation')->useCurrent();
                $table->timestamp('date_decision')->nullable();
                $table->foreignId('id_admin_decideur')->nullable();
                $table->text('notes_admin')->nullable();
            });
        }
    }
}
