<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MicrosoftSsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MicrosoftSsoController extends Controller
{
    public function __construct(private readonly MicrosoftSsoService $microsoftSsoService) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->microsoftSsoService->isConfigured()) {
            return redirect()->route('login')->with('error', 'La connexion Microsoft n’est pas encore configurée.');
        }

        $state = Str::random(64);
        $codeVerifier = Str::random(96);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $request->session()->put('microsoft_sso', [
            'state' => $state,
            'code_verifier' => $codeVerifier,
        ]);

        return redirect()->away($this->microsoftSsoService->authorizationUrl($state, $codeChallenge));
    }

    public function callback(Request $request): RedirectResponse
    {
        $flow = $request->session()->pull('microsoft_sso', []);
        $expectedState = (string) ($flow['state'] ?? '');
        $receivedState = $request->string('state')->toString();

        if ($expectedState === '' || $receivedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->route('login')->with('error', 'La tentative de connexion Microsoft a expiré ou est invalide.');
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->with('error', 'La connexion Microsoft a été annulée ou refusée.');
        }

        try {
            $profile = $this->microsoftSsoService->authenticatedUser(
                $request->string('code')->toString(),
                (string) ($flow['code_verifier'] ?? ''),
            );

            $user = $this->resolveUser($profile);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'La connexion Microsoft n’a pas pu être finalisée. Réessayez ou contactez l’administrateur.');
        }

        if (! $user) {
            return redirect()->route('login')->with('error', 'Votre compte Microsoft n’est pas autorisé sur ce portail. Contactez un administrateur.');
        }

        if (! $user->is_active) {
            return redirect()->route('login')->with('error', 'Votre compte a été désactivé. Veuillez contacter l’administrateur.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('auth.last_activity_at', now()->timestamp);

        return redirect()->intended(route('home'));
    }

    /**
     * @param  array{id: string, name: string, email: string}  $profile
     */
    private function resolveUser(array $profile): ?User
    {
        return DB::transaction(function () use ($profile): ?User {
            $isConfiguredAdministrator = in_array($profile['email'], $this->microsoftSsoService->administratorEmails(), true);
            $user = User::query()->where('microsoft_id', $profile['id'])->first();

            if (! $user) {
                $user = User::query()
                    ->whereRaw('LOWER(email) = ?', [$profile['email']])
                    ->first();
            }

            if (! $user && ! $isConfiguredAdministrator) {
                return null;
            }

            if (! $user) {
                $user = new User([
                    'email' => $profile['email'],
                    'role' => 'admin',
                    'is_active' => true,
                ]);
            }

            if (filled($user->microsoft_id) && $user->microsoft_id !== $profile['id']) {
                return null;
            }

            $emailBelongsToAnotherUser = User::query()
                ->where('email', $profile['email'])
                ->when($user->exists, fn ($query) => $query->where('id', '!=', $user->id))
                ->exists();

            $user->forceFill([
                'name' => $profile['name'],
                'email' => $emailBelongsToAnotherUser ? $user->email : $profile['email'],
                'microsoft_id' => $profile['id'],
                'email_verified_at' => $user->email_verified_at ?: now(),
                'last_sso_login_at' => now(),
                ...$isConfiguredAdministrator ? ['role' => 'admin'] : [],
            ])->save();

            return $user;
        });
    }
}
