<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class SalesforceService
{
    protected string $tokenUrl;

    protected string $clientId;

    protected string $clientSecret;

    protected string $apiBase;

    protected string $apiVersion;

    public function __construct()
    {
        $this->tokenUrl = config('services.salesforce.token_url');
        $this->clientId = config('services.salesforce.client_id');
        $this->clientSecret = config('services.salesforce.client_secret');
        $this->apiBase = config('services.salesforce.api_base');
        $this->apiVersion = config('services.salesforce.api_version');
    }

    public function getAccessToken(): string
    {
        return Cache::remember('salesforce_token', 3600, function (): string {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return (string) $response->json('access_token');
            }

            throw new RuntimeException('Impossible de récupérer le jeton Salesforce. Statut HTTP : '.$response->status());
        });
    }

    public function getOpportunities(): Collection
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->get("{$this->apiBase}/services/data/{$this->apiVersion}/query", [
                    'q' => 'SELECT Id, Name, Account.Name FROM Opportunity WHERE IsWon = true ORDER BY Name ASC',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return collect($data['records'] ?? [])->map(function (array $record): array {
                    return [
                        'id' => $record['Id'],
                        'name' => $record['Name'],
                        'account_name' => $record['Account']['Name'] ?? '',
                    ];
                })->values();
            }

            return collect([]);
        } catch (\Throwable $exception) {
            report($exception);

            return collect([]);
        }
    }

    public function getOpportunityById(string $id): ?array
    {
        if (! preg_match('/^[a-zA-Z0-9]{15}(?:[a-zA-Z0-9]{3})?$/', $id)) {
            throw new InvalidArgumentException('Identifiant Salesforce invalide.');
        }

        try {
            $token = $this->getAccessToken();

            $query = "SELECT Id, Name, Account.Name FROM Opportunity WHERE Id = '{$id}'";

            $response = Http::withToken($token)
                ->get("{$this->apiBase}/services/data/{$this->apiVersion}/query", [
                    'q' => $query,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (! empty($data['records'])) {
                    return [
                        'id' => $data['records'][0]['Id'],
                        'name' => $data['records'][0]['Name'],
                        'account_name' => $data['records'][0]['Account']['Name'] ?? '',
                    ];
                }
            }

            return null;
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public function getAccounts(): Collection
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->get("{$this->apiBase}/services/data/{$this->apiVersion}/query", [
                    'q' => 'SELECT Id, Name FROM Account WHERE IsDeleted = false ORDER BY Name ASC',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return collect($data['records'] ?? [])->map(function (array $record): array {
                    return [
                        'id' => $record['Id'],
                        'name' => $record['Name'],
                    ];
                })->values();
            }

            return collect([]);
        } catch (\Throwable $exception) {
            report($exception);

            return collect([]);
        }
    }
}
