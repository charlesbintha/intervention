<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MicrosoftSsoService
{
    public function isConfigured(): bool
    {
        return filled(config('services.microsoft_sso.tenant_id'))
            && filled(config('services.microsoft_sso.client_id'))
            && filled(config('services.microsoft_sso.client_secret'));
    }

    public function authorizationUrl(string $state, string $codeChallenge): string
    {
        $parameters = [
            'client_id' => config('services.microsoft_sso.client_id'),
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri(),
            'response_mode' => 'query',
            'scope' => config('services.microsoft_sso.scope'),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];

        if (filled(config('services.microsoft_sso.prompt'))) {
            $parameters['prompt'] = config('services.microsoft_sso.prompt');
        }

        return $this->tenantEndpoint('authorize').'?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    /** @return array{id: string, name: string, email: string} */
    public function authenticatedUser(string $authorizationCode, string $codeVerifier): array
    {
        $tokenResponse = Http::asForm()->timeout(20)->post($this->tenantEndpoint('token'), [
            'client_id' => config('services.microsoft_sso.client_id'),
            'client_secret' => config('services.microsoft_sso.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $this->redirectUri(),
            'scope' => config('services.microsoft_sso.scope'),
            'code_verifier' => $codeVerifier,
        ]);

        $accessToken = $tokenResponse->json('access_token');
        if (! $tokenResponse->successful() || blank($accessToken)) {
            throw new RuntimeException('Microsoft a refusé le code de connexion.');
        }

        $profileResponse = Http::withToken((string) $accessToken)
            ->acceptJson()
            ->timeout(20)
            ->get('https://graph.microsoft.com/v1.0/me', [
                '$select' => 'id,displayName,mail,userPrincipalName',
            ]);

        if (! $profileResponse->successful()) {
            throw new RuntimeException('Impossible de récupérer le profil Microsoft.');
        }

        $email = mb_strtolower(trim((string) ($profileResponse->json('mail') ?: $profileResponse->json('userPrincipalName'))));
        $microsoftId = trim((string) $profileResponse->json('id'));

        if ($microsoftId === '' || $email === '') {
            throw new RuntimeException('Le compte Microsoft ne fournit pas d’identifiant ou d’adresse email exploitable.');
        }

        return [
            'id' => $microsoftId,
            'name' => trim((string) ($profileResponse->json('displayName') ?: $email)),
            'email' => $email,
        ];
    }

    /** @return list<string> */
    public function administratorEmails(): array
    {
        return collect(explode(',', (string) config('services.microsoft_sso.admin_emails')))
            ->map(fn (string $email): string => mb_strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function logoutUrl(): string
    {
        return $this->tenantEndpoint('logout').'?'.http_build_query([
            'post_logout_redirect_uri' => route('login'),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function redirectUri(): string
    {
        return (string) (config('services.microsoft_sso.redirect_uri') ?: route('auth.microsoft.callback'));
    }

    private function tenantEndpoint(string $endpoint): string
    {
        $tenantId = rawurlencode((string) config('services.microsoft_sso.tenant_id'));

        return "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/{$endpoint}";
    }
}
