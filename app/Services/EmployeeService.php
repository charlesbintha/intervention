<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EmployeeService
{
    protected $apiUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.employees.api_url');
        $this->apiKey = config('services.employees.api_key');
    }

    public function getEmployees()
    {
        return Cache::remember('employees_list', 3600, function () {
            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])->get($this->apiUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    // L'API retourne un objet avec la clé 'data'
                    $employees = $data['data'] ?? $data;

                    return collect($employees)->map(function ($employee) {
                        return [
                            'id' => $employee['id'] ?? 0,
                            'nom' => $employee['nom'] ?? '',
                            'prenom' => $employee['prenom'] ?? '',
                            'email' => $employee['email'] ?? '',
                            'telephone' => $employee['telephone'] ?? '',
                            'prenom_nom' => $employee['prenom_nom'] ?? (($employee['prenom'] ?? '').' '.($employee['nom'] ?? '')),
                        ];
                    })->filter(function ($employee) {
                        return $employee['id'] > 0 && ! empty($employee['prenom_nom']);
                    })->sortBy('prenom_nom')->values();
                }

                return collect([]);
            } catch (\Exception $e) {
                \Log::error('Employees API Error: '.$e->getMessage());

                return collect([]);
            }
        });
    }

    public function getEmployeeById($id)
    {
        $employees = $this->getEmployees();

        return $employees->firstWhere('id', $id);
    }
}
