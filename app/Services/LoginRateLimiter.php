<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginRateLimiter
{
    public function tooManyAttempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts($this->key($request), $this->maxAttempts());
    }

    public function hit(Request $request): void
    {
        RateLimiter::hit($this->key($request), $this->decaySeconds());
    }

    public function clear(Request $request): void
    {
        RateLimiter::clear($this->key($request));
    }

    public function availableIn(Request $request): int
    {
        return RateLimiter::availableIn($this->key($request));
    }

    private function key(Request $request): string
    {
        $email = Str::transliterate(Str::lower((string) $request->input('email')));

        return "login|{$email}|{$request->ip()}";
    }

    private function maxAttempts(): int
    {
        return (int) config('security.login.max_attempts', 5);
    }

    private function decaySeconds(): int
    {
        return (int) config('security.login.decay_seconds', 300);
    }
}
