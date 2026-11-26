<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_login_but_cannot_view_dashboard(): void
    {
        $this->get('/login')->assertOk()->assertSee('Selamat datang kembali');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'secret123', 'remember' => '1'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk()->assertSee('Admin');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_invalid_password_is_rejected(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret123']);
        $this->from('/login')->post('/login', ['email' => 'admin@example.com', 'password' => 'wrong'])->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_repeated_failed_logins_are_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'unknown@example.com', 'password' => 'wrong']);
        }
        $response = $this->post('/login', ['email' => 'unknown@example.com', 'password' => 'wrong']);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan.', session('errors')->first('email'));
        $this->assertGuest();
    }
}
