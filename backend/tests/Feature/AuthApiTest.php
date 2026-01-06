<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_token(): void {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret-password',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $user = User::where('email', $payload['email'])->firstOrFail();
        $this->assertTrue(Hash::check($payload['password'], $user->password));
    }

    public function test_login_returns_token_for_valid_credentials(): void {
        $password = 'login-password';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['token']);
    }

    public function test_logout_revokes_current_token(): void {
        $user = User::factory()->create();
        $token = $user->createToken('api-token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
