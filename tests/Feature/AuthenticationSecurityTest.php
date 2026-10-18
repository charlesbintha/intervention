<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('expires an inactive web session', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->withSession(['auth.last_activity_at' => now()->subMinutes(31)->timestamp])
        ->get(route('home'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Votre session a expiré pour cause d’inactivité. Veuillez vous reconnecter.');

    $this->assertGuest();
});

it('only offers Microsoft authentication on the login page', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Se connecter avec Microsoft')
        ->assertDontSee('name="password"', false)
        ->assertDontSee('name="email"', false);
});

it('starts Microsoft SSO with state and PKCE', function () {
    config([
        'services.microsoft_sso.tenant_id' => 'tenant-id',
        'services.microsoft_sso.client_id' => 'client-id',
        'services.microsoft_sso.client_secret' => 'client-secret',
        'services.microsoft_sso.redirect_uri' => 'https://portal.test/auth/microsoft/callback',
    ]);

    $response = $this->get(route('auth.microsoft.redirect'));

    $response->assertRedirectContains('https://login.microsoftonline.com/tenant-id/oauth2/v2.0/authorize')
        ->assertSessionHas('microsoft_sso');

    expect(session('microsoft_sso.state'))->not->toBeEmpty()
        ->and(session('microsoft_sso.code_verifier'))->not->toBeEmpty();
});

it('links an authorized local user to their Microsoft identity', function () {
    config([
        'services.microsoft_sso.tenant_id' => 'tenant-id',
        'services.microsoft_sso.client_id' => 'client-id',
        'services.microsoft_sso.client_secret' => 'client-secret',
        'services.microsoft_sso.redirect_uri' => 'https://portal.test/auth/microsoft/callback',
    ]);
    Http::fake([
        'https://login.microsoftonline.com/*' => Http::response(['access_token' => 'access-token']),
        'https://graph.microsoft.com/v1.0/me*' => Http::response([
            'id' => 'microsoft-user-id',
            'displayName' => 'Agent Microsoft',
            'mail' => 'agent@example.com',
        ]),
    ]);
    $user = User::factory()->create(['email' => 'agent@example.com', 'is_active' => true]);

    $this->withSession([
        'microsoft_sso' => ['state' => 'valid-state', 'code_verifier' => 'valid-verifier'],
    ])->get(route('auth.microsoft.callback', ['state' => 'valid-state', 'code' => 'authorization-code']))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->microsoft_id)->toBe('microsoft-user-id')
        ->and($user->fresh()->last_sso_login_at)->not->toBeNull();
});

it('rejects a Microsoft identity that was not created by an administrator', function () {
    configureMicrosoftSso();
    fakeMicrosoftProfile('unknown-id', 'unknown@example.com');

    $this->withSession([
        'microsoft_sso' => ['state' => 'valid-state', 'code_verifier' => 'valid-verifier'],
    ])->get(route('auth.microsoft.callback', ['state' => 'valid-state', 'code' => 'authorization-code']))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Votre compte Microsoft n’est pas autorisé sur ce portail. Contactez un administrateur.');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'unknown@example.com']);
});

it('creates the first configured Microsoft administrator without a password', function () {
    configureMicrosoftSso(['services.microsoft_sso.admin_emails' => 'admin@example.com']);
    fakeMicrosoftProfile('admin-id', 'admin@example.com', 'Administrateur Microsoft');

    $this->withSession([
        'microsoft_sso' => ['state' => 'valid-state', 'code_verifier' => 'valid-verifier'],
    ])->get(route('auth.microsoft.callback', ['state' => 'valid-state', 'code' => 'authorization-code']))
        ->assertRedirect(route('home'));

    $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($administrator);
    expect($administrator->role)->toBe('admin')
        ->and($administrator->password)->toBeNull()
        ->and($administrator->microsoft_id)->toBe('admin-id');
});

it('rejects an invalid Microsoft state before exchanging the authorization code', function () {
    configureMicrosoftSso();
    Http::fake();

    $this->withSession([
        'microsoft_sso' => ['state' => 'expected-state', 'code_verifier' => 'valid-verifier'],
    ])->get(route('auth.microsoft.callback', ['state' => 'different-state', 'code' => 'authorization-code']))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', 'La tentative de connexion Microsoft a expiré ou est invalide.');

    Http::assertNothingSent();
});

it('rejects password authentication through the web and api', function () {
    $this->post('/login', ['email' => 'agent@example.com', 'password' => 'password'])->assertMethodNotAllowed();
    $this->postJson('/api/login', ['email' => 'agent@example.com', 'password' => 'password'])->assertNotFound();
});

it('rejects and revokes an api token belonging to a disabled account', function () {
    $user = User::factory()->create(['is_active' => false]);
    $token = $user->createToken('mobile-app')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertForbidden()
        ->assertJsonPath('message', 'Votre compte a été désactivé.');

    expect($user->tokens()->count())->toBe(0);
});

it('adds security headers to authenticated pages', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('does not expose files outside the attachments directory', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->get('/attachments/%2e%2e/%2e%2e/.env')
        ->assertNotFound();
});

/** @param array<string, mixed> $overrides */
function configureMicrosoftSso(array $overrides = []): void
{
    config(array_merge([
        'services.microsoft_sso.tenant_id' => 'tenant-id',
        'services.microsoft_sso.client_id' => 'client-id',
        'services.microsoft_sso.client_secret' => 'client-secret',
        'services.microsoft_sso.redirect_uri' => 'https://portal.test/auth/microsoft/callback',
        'services.microsoft_sso.admin_emails' => '',
    ], $overrides));
}

function fakeMicrosoftProfile(string $id, string $email, string $name = 'Utilisateur Microsoft'): void
{
    Http::fake([
        'https://login.microsoftonline.com/*' => Http::response(['access_token' => 'access-token']),
        'https://graph.microsoft.com/v1.0/me*' => Http::response([
            'id' => $id,
            'displayName' => $name,
            'mail' => $email,
        ]),
    ]);
}
