<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidatePasswordSession
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            $current = hash('sha256', $user->getAuthPassword());
            $stored = $request->session()->get('auth_password_fingerprint');
            // Remember cookies are validated by the guard using the rotated remember token.
            if (Auth::viaRemember()) {
                $request->session()->put('auth_password_fingerprint', $current);
            } elseif (! is_string($stored) || ! hash_equals($current, $stored)) {
                Auth::logoutCurrentDevice();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Sesi berakhir. Silakan masuk kembali.');
            }
        }

        return $next($request);
    }
}
