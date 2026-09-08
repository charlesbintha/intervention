<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProjectService
{
    protected $apiUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.projects.api_url');
        $this->apiKey = config('services.projects.api_key');
    }

    public function getProjects(): Collection
    {
        return Cache::remember('projects_list_v3', 3600, function () {
            try {
                $response = Http::withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])->get($this->apiUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    $projects = $data['items'] ?? $data;

                    $mapped = collect($projects)->map(function ($project) {
                        $code = $project['code_projet'] ?? '';
                        $nom = $project['nom_projet'] ?? '';

                        $mapped = [
                            'id' => $project['id'] ?? null,
                            'code_projet' => $code,
                            'nom_projet' => $nom,
                            'display' => $code.' - '.$nom,
                            'opportunity_id' => $project['sf_opportunity_id'] ?? '',
                            'client_name' => $project['client_name']
                                ?? $project['nom_client']
                                ?? $project['account_name']
                                ?? data_get($project, 'client.name')
                                ?? '',
                            'location' => $project['location']
                                ?? $project['lieu']
                                ?? $project['site']
                                ?? null,
                            'description' => $project['objectif_projet']
                                ?? $project['contexte']
                                ?? $project['synthese']
                                ?? null,
                            'start_date' => $project['date_demarrage'] ?? null,
                            'end_date' => $project['date_fin'] ?? null,
                            'ms_group_id' => $project['ms_group_id'] ?? null,
                            'ms_plan_id' => $project['ms_plan_id'] ?? null,
                            'ms_bucket_id' => $project['ms_bucket_id'] ?? null,
                            'project_status' => $project['statut_initial'] ?? '',
                            'executing_subsidiary_name' => $project['filiale_executant'] ?? '',
                            'subsidiary' => $this->resolveSubsidiaryCode($project['filiale_executant'] ?? ''),
                            'is_deleted' => ! empty($project['deleted_at']),
                        ];

                        return $mapped;
                    })->filter(function ($project) {
                        return ! empty($project['code_projet']);
                    })->values();

                    return $mapped;
                }

                \Log::warning('Projects API returned non-successful response');

                return collect([]);
            } catch (\Exception $e) {
                \Log::error('Projects API Error: '.$e->getMessage());

                return collect([]);
            }
        });
    }

    public function getTrackableProjects(): Collection
    {
        $allowedStatuses = [
            'en cours',
            'en cours de traitement',
            'planifie',
            'planifier',
            'pause',
            'en pause',
        ];

        return $this->getProjects()
            ->filter(function (array $project) use ($allowedStatuses): bool {
                $status = Str::lower(Str::ascii(trim($project['project_status'])));

                return ! $project['is_deleted'] && in_array($status, $allowedStatuses, true);
            })
            ->values();
    }

    public function getProjectByCode(string $code): ?array
    {
        $projects = $this->getProjects();

        return $projects->firstWhere('code_projet', $code);
    }

    public function getTrackableProjectByCode(string $code): ?array
    {
        return $this->getTrackableProjects()->firstWhere('code_projet', $code);
    }

    private function resolveSubsidiaryCode(string $executingSubsidiary): ?string
    {
        return match (Str::lower(Str::ascii(trim($executingSubsidiary)))) {
            'gut', 'groupe univers telecom' => 'GUT',
            'cp', 'cabinet pencco' => 'CP',
            'uta', 'univers telecom afrique' => 'UTA',
            'ua', 'univers academy' => 'UA',
            'ute', 'univers technology & energy', 'univers technology & energies' => 'UTE',
            'uc', 'univers capital' => 'UC',
            default => null,
        };
    }
}
