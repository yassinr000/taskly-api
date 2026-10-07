<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_a_token(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ali', 'email' => 'ali@test.com',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);
    }

    public function test_short_password_is_rejected(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ali', 'email' => 'ali@test.com',
            'password' => '12345', 'password_confirmation' => '12345',
        ])->assertUnprocessable();
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
             ->assertUnauthorized();
    }

    public function test_protected_route_requires_a_token(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
