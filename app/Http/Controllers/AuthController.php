<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email belum diisi.',
            'email.email' => 'Format emailnya belum benar.',
            'password.required' => 'Kata sandi belum diisi.',
        ]);

        // Tanpa pembatas ini, form login satu pemilik jadi pintu coba-coba sandi.
        $kunci = Str::transliterate(Str::lower($kredensial['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi '.RateLimiter::availableIn($kunci).' detik lagi.',
            ]);
        }

        if (! Auth::attempt($kredensial, $request->boolean('remember'))) {
            RateLimiter::hit($kunci, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandinya tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
