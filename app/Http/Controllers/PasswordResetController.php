<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $credentials = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $identity = hash_hmac('sha256', $credentials['email'], config('app.key'));
        $limitKey = 'password-reset-hour:'.$identity;
        if (! RateLimiter::tooManyAttempts($limitKey, 3)) {
            RateLimiter::hit($limitKey, 3600);
            try {
                $result = Password::sendResetLink($credentials);
                Log::info('password_reset.request', ['account' => $identity, 'result' => $result]);
            } catch (TransportExceptionInterface $exception) {
                // Do not log the exception: SMTP errors can contain recipient data or message content.
                Log::warning('password_reset.delivery_failed', ['account' => $identity]);
            }
        } else {
            Log::notice('password_reset.account_limited', ['account' => $identity]);
        }

        return back()->with('status', 'Jika email terdaftar, tautan reset kata sandi akan dikirim. Periksa kotak masuk dan folder spam. Tunggu 60 detik sebelum meminta ulang.');
    }

    public function edit(Request $request, string $token)
    {
        return response()->view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')])
            ->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function update(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', 'max:4096', PasswordRule::min(12)->letters()->numbers()],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.letters' => 'Kata sandi harus mengandung huruf.',
            'password.numbers' => 'Kata sandi harus mengandung angka.',
        ]);

        $changedUser = null;
        $status = Password::reset($credentials, function (User $user, string $password) use (&$changedUser) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
            $changedUser = $user;
        });

        if ($status !== Password::PASSWORD_RESET) {
            Log::notice('password_reset.rejected');
            throw ValidationException::withMessages(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        Log::info('password_reset.completed', ['user_id' => $changedUser->id]);
        try {
            $changedUser->notify(new PasswordChangedNotification);
        } catch (TransportExceptionInterface $exception) {
            Log::warning('password_reset.confirmation_delivery_failed', ['user_id' => $changedUser->id]);
        }

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.');
    }
}
