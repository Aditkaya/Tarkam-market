<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification as ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'OldPassword123']);
    }

    private function submit(string $token, string $email = 'admin@example.com')
    {
        Notification::fake();
        return $this->post('/reset-password', ['token' => $token, 'email' => $email,
            'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123']);
    }

    public function test_forms_and_reset_notification_work(): void
    {
        Notification::fake();
        $user = $this->user();
        $this->get('/login')->assertSee('Lupa kata sandi?');
        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => 'ADMIN@example.com'])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->assertTrue(Password::tokenExists($user, $notification->token));
            $this->assertStringContainsString('/reset-password/', $notification->toMail($user)->actionUrl);
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
                ->assertOk()->assertSee('Buat kata sandi baru')->assertHeader('Cache-Control', 'no-store, private');
            return true;
        });
    }

    public function test_unknown_email_has_same_response_and_requests_are_throttled(): void
    {
        Notification::fake();
        $this->user();
        $this->post('/forgot-password', ['email' => 'admin@example.com']);
        $message = session('status');
        $this->post('/forgot-password', ['email' => 'unknown@example.com'])->assertSessionHas('status', $message);
        $this->post('/forgot-password', ['email' => 'admin@example.com']);
        Notification::assertCount(1);
        for ($i = 0; $i < 2; $i++) {
            $this->post('/forgot-password', ['email' => 'unknown@example.com']);
        }
        $this->post('/forgot-password', ['email' => 'unknown@example.com'])->assertStatus(429);
    }

    public function test_password_changes_and_token_cannot_be_reused(): void
    {
        $user = $this->user();
        $user->forceFill(['remember_token' => 'old-remember-token'])->save();
        $token = Password::createToken($user);
        $this->submit($token)->assertRedirect('/login')->assertSessionHas('status');
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertNotSame('old-remember-token', $user->fresh()->remember_token);
        $this->assertGuest();
        Notification::assertSentTo($user, \App\Notifications\PasswordChangedNotification::class);
        $this->submit($token)->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'OldPassword123'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'NewPassword123'])->assertRedirect('/dashboard');
    }

    public function test_invalid_expired_and_wrong_account_tokens_are_rejected(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        $this->submit('invalid-token')->assertSessionHasErrors('email');
        User::create(['name' => 'Other', 'email' => 'other@example.com', 'password' => 'OldPassword123']);
        $this->submit($token, 'other@example.com')->assertSessionHasErrors('email');
        $this->travel(61)->minutes();
        $this->submit($token)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_password_confirmation_and_strength_are_required(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        $this->post('/reset-password', ['token' => $token, 'email' => $user->email,
            'password' => 'short', 'password_confirmation' => 'different'])->assertSessionHasErrors('password');
        $this->assertTrue(Password::tokenExists($user, $token));
    }

    public function test_reset_email_can_be_rendered_and_delivered_to_mail_transport(): void
    {
        config(['mail.default' => 'array']);
        $user = $this->user();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $message = $messages->first()->getOriginalMessage();
        $this->assertSame($user->email, $message->getTo()[0]->getAddress());
        $this->assertStringContainsString('Buat kata sandi baru', $message->getHtmlBody());
    }

    public function test_old_session_is_rejected_after_reset_and_new_login_still_works(): void
    {
        $user = $this->user();
        $this->post('/login', ['email' => $user->email, 'password' => 'OldPassword123'])->assertRedirect('/dashboard');
        $oldSession = session()->all();
        $this->get('/dashboard')->assertOk();
        // A separate client resets the password; restore the first client's session afterwards.
        $this->app['auth']->forgetGuards();
        session()->flush();
        $this->submit(Password::createToken($user))->assertRedirect('/login');
        $this->app['auth']->forgetGuards();
        session()->flush();
        $this->withSession($oldSession)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'NewPassword123'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
    }

    public function test_legacy_session_without_fingerprint_must_log_in_again(): void
    {
        $user = $this->user();
        $this->withSession([\Illuminate\Support\Facades\Auth::guard()->getName() => $user->id])
            ->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_account_limit_applies_across_different_ip_addresses(): void
    {
        Notification::fake();
        $user = $this->user();
        for ($i = 1; $i <= 4; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.$i])
                ->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
            $this->travel(61)->seconds();
        }
        Notification::assertSentToTimes($user, ResetPassword::class, 3);
        $this->travel(60)->minutes();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentToTimes($user, ResetPassword::class, 4);
    }

    public function test_smtp_failure_has_generic_response_and_no_sensitive_log_data(): void
    {
        $this->user();
        Password::shouldReceive('sendResetLink')->once()->andThrow(new \Symfony\Component\Mailer\Exception\TransportException('SECRET_TOKEN'));
        \Illuminate\Support\Facades\Log::spy();
        $this->post('/forgot-password', ['email' => 'admin@example.com'])->assertRedirect()->assertSessionHas('status');
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->with('password_reset.delivery_failed', \Mockery::on(function ($context) {
            return array_keys($context) === ['account'] && ! str_contains(json_encode($context), 'admin@example.com');
        }));
    }

    public function test_confirmation_failure_does_not_undo_reset_or_preserve_token(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        Notification::shouldReceive('send')->once()->andThrow(new \Symfony\Component\Mailer\Exception\TransportException('SMTP failed'));
        $this->post('/reset-password', ['token' => $token, 'email' => $user->email,
            'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123'])
            ->assertRedirect('/login')->assertSessionHas('status');
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertFalse(Password::tokenExists($user, $token));
    }

    public function test_reset_url_uses_configured_domain_instead_of_request_host(): void
    {
        config(['app.url' => 'https://accounts.example.com']);
        $user = $this->user();
        $mail = (new ResetPassword('test-token'))->toMail($user);
        $this->assertStringStartsWith('https://accounts.example.com/reset-password/', $mail->actionUrl);
    }
}
