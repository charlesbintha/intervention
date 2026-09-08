<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('login|agent@example.com|127.0.0.1');
});

it('expires an inactive web session', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->withSession(['auth.last_activity_at' => now()->subMinutes(31)->timestamp])
        ->get(route('home'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Votre session a expiré pour cause d’inactivité. Veuillez vous reconnecter.');

    $this->assertGuest();
});

it('limits repeated failed login attempts', function () {
    User::factory()->create([
        'email' => 'agent@example.com',
        'password' => Hash::make('correct-password'),
        'is_active' => true,
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => 'agent@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $response = $this->post(route('login'), [
        'email' => 'agent@example.com',
        'password' => 'correct-password',
    ])->assertSessionHasErrors('email');

    expect($response->getSession()->get('errors')->first('email'))->toContain('Trop de tentatives');

    $this->assertGuest();
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
