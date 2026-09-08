<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\LoginRateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request, LoginRateLimiter $loginRateLimiter): RedirectResponse
    {
        if ($loginRateLimiter->tooManyAttempts($request)) {
            return back()->withErrors([
                'email' => 'Trop de tentatives. Réessayez dans '.$loginRateLimiter->availableIn($request).' seconde(s).',
            ])->onlyInput('email');
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            $loginRateLimiter->hit($request);

            return back()->withErrors([
                'email' => 'Les informations d’identification ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            $loginRateLimiter->hit($request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Votre compte a été désactivé. Veuillez contacter l’administrateur.');
        }

        $loginRateLimiter->clear($request);
        $request->session()->regenerate();
        $request->session()->put('auth.last_activity_at', now()->timestamp);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
