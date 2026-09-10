<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_is_sent_to_inactive_screen_after_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // El login autentica, pero el middleware lo expulsa en la siguiente petición.
        $this->followRedirects($response)->assertSee('Tu cuenta está inactiva');
        $this->assertGuest();
    }

    public function test_active_session_is_kicked_out_when_user_is_deactivated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user->fresh())
            ->get('/dashboard')
            ->assertRedirect(route('account.inactive'));

        $this->assertGuest();
    }

    public function test_inactive_screen_is_publicly_accessible(): void
    {
        $this->get(route('account.inactive'))
            ->assertOk()
            ->assertSee('Tu cuenta está inactiva');
    }
}
