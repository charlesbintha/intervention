<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProjectService
{
    protected $apiUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.projects.api_url');
        $this->apiKey = config('services.projects.api_key');
    }

    public function getProjects()
    {
        return Cache::remember('projects_list', 3600, function () {
            try {
                \Log::info('=== DEBUT RECUPERATION PROJETS ===');
                \Log::info('API URL: '.$this->apiUrl);

                $response = Http::withoutVerifying()->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])->get($this->apiUrl);

                \Log::info('Response Status: '.$response->status());

                if ($response->successful()) {
                    $data = $response->json();
                    \Log::info('Raw API Response (first 3 items):', ['data' => array_slice($data['items'] ?? $data, 0, 3)]);

                    // L'API retourne un objet avec la clé 'items'
                    $projects = $data['items'] ?? $data;

                    $mapped = collect($projects)->map(function ($project) {
                        $code = $project['code_projet'] ?? '';
                        $nom = $project['nom_projet'] ?? '';

                        $mapped = [
                            'code_projet' => $code,
                            'nom_projet' => $nom,
                            'display' => $code.' - '.$nom,
                            'opportunity_id' => $project['sf_opportunity_id'] ?? '',
                            'client_name' => $project['client_name']
                                ?? $project['nom_client']
                                ?? $project['account_name']
                                ?? data_get($project, 'client.name')
                                ?? '',
                        ];

                        // Log chaque projet avec son opportunity_id
                        if ($mapped['opportunity_id']) {
                            \Log::info('Project with opportunity_id', [
                                'code' => $mapped['code_projet'],
                                'nom' => $mapped['nom_projet'],
                                'opportunity_id' => $mapped['opportunity_id'],
                            ]);
                        }

                        return $mapped;
                    })->filter(function ($project) {
                        return ! empty($project['code_projet']);
                    })->values();

                    \Log::info('Total projects mapped: '.$mapped->count());
                    \Log::info('Projects with opportunity_id: '.$mapped->whereNotNull('opportunity_id')->count());
                    \Log::info('=== FIN RECUPERATION PROJETS ===');

                    return $mapped;
                }

                \Log::warning('Projects API returned non-successful response');

                return collect([]);
            } catch (\Exception $e) {
                \Log::error('Projects API Error: '.$e->getMessage());
                \Log::error('Stack trace: '.$e->getTraceAsString());

                return collect([]);
            }
        });
    }

    public function getProjectByCode($code)
    {
        $projects = $this->getProjects();

        return $projects->firstWhere('code_projet', $code);
    }
}
