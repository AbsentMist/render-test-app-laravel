<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tests feature pour l'integration Payrexx.
 *
 * @author Alessandro Neris
 * @return void
 */
class PayrexxTest extends TestCase
{

    public function test_authenticated_user_can_create_gateway()
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['link' => 'https://payrexx.test/gateway/abc'],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        Participant::factory()->create(['id_user' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/paiement/gateway', [
                'montant' => 19.9,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['url' => 'https://payrexx.test/gateway/abc']);
    }

    public function test_gateway_validation_fails_when_amount_is_missing()
    {
        $user = User::factory()->create();
        Participant::factory()->create(['id_user' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/paiement/gateway', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['montant']);
    }

    public function test_gateway_returns_500_when_provider_fails()
    {
        Http::fake([
            '*' => Http::response(['error' => 'failure'], 500),
        ]);

        $user = User::factory()->create();
        Participant::factory()->create(['id_user' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/paiement/gateway', [
                'montant' => 10,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['message']);
    }
}
