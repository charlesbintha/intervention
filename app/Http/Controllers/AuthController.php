<?php

namespace App\Http\Controllers;

use App\Services\MicrosoftSsoService;
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

    public function logout(Request $request, MicrosoftSsoService $microsoftSsoService): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($microsoftSsoService->isConfigured()) {
            return redirect()->away($microsoftSsoService->logoutUrl());
        }

        return redirect()->route('login');
    }
}
