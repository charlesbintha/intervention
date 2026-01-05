<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SalesforceService
{
    protected $tokenUrl;
    protected $clientId;
    protected $clientSecret;
    protected $apiBase;
    protected $apiVersion;

    public function __construct()
    {
        $this->tokenUrl = config('services.salesforce.token_url');
        $this->clientId = config('services.salesforce.client_id');
        $this->clientSecret = config('services.salesforce.client_secret');
        $this->apiBase = config('services.salesforce.api_base');
        $this->apiVersion = config('services.salesforce.api_version');
    }

    public function getAccessToken()
    {
        return Cache::remember('salesforce_token', 3600, function () {
            $response = Http::withoutVerifying()->asForm()->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            throw new \Exception('Failed to get Salesforce access token: ' . $response->body());
        });
    }

    public function getOpportunities()
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withoutVerifying()->withToken($token)
                ->get("{$this->apiBase}/services/data/{$this->apiVersion}/query", [
                    'q' => "SELECT Id, Name, Account.Name FROM Opportunity WHERE IsWon = true ORDER BY Name ASC"
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return collect($data['records'] ?? [])->map(function ($record) {
                    return [
                        'id' => $record['Id'],
                        'name' => $record['Name'],
                        'account_name' => $record['Account']['Name'] ?? '',
                    ];
                })->values();
            }

            return collect([]);
        } catch (\Exception $e) {
            \Log::error('Salesforce API Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function getOpportunityById($id)
    {
        try {
            \Log::info('=== DEBUT RECUPERATION OPPORTUNITY ===');
            \Log::info('Opportunity ID: ' . $id);

            $token = $this->getAccessToken();
            \Log::info('Access token obtained');

            $query = "SELECT Id, Name, Account.Name FROM Opportunity WHERE Id = '{$id}'";
            \Log::info('SOQL Query: ' . $query);

            $response = Http::withoutVerifying()->withToken($token)
                ->get("{$this->apiBase}/services/data/{$this->apiVersion}/query", [
                    'q' => $query
                ]);

            \Log::info('Salesforce Response Status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();
                \Log::info('Salesforce Response Data:', $data);

                if (!empty($data['records'])) {
                    $result = [
                        'id' => $data['records'][0]['Id'],
                        'name' => $data['records'][0]['Name'],
                        'account_name' => $data['records'][0]['Account']['Name'] ?? '',
                    ];
                    \Log::info('Opportunity found:', $result);
                    \Log::info('=== FIN RECUPERATION OPPORTUNITY (SUCCESS) ===');
                    return $result;
                } else {
                    \Log::warning('No records found for opportunity ID: ' . $id);
                }
            } else {
                \Log::error('Salesforce API returned non-successful status', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            \Log::info('=== FIN RECUPERATION OPPORTUNITY (NULL) ===');
            return null;
        } catch (\Exception $e) {
            \Log::error('Salesforce API Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::info('=== FIN RECUPERATION OPPORTUNITY (ERROR) ===');
            return null;
        }
    }

    public function getAccounts()
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withoutVerifying()->withToken($token)
                ->get("{$this->apiBase}/services/data/{$this->apiVersion}/query", [
                    'q' => "SELECT Id, Name FROM Account WHERE IsDeleted = false ORDER BY Name ASC"
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return collect($data['records'] ?? [])->map(function ($record) {
                    return [
                        'id' => $record['Id'],
                        'name' => $record['Name'],
                    ];
                })->values();
            }

            return collect([]);
        } catch (\Exception $e) {
            \Log::error('Salesforce API Error: ' . $e->getMessage());
            return collect([]);
        }
    }
}
