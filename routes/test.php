<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\EmployeeService;
use App\Services\ProjectService;
use App\Services\SalesforceService;

Route::get('/test/employees', function (EmployeeService $service) {
    $employees = $service->getEmployees();
    return response()->json([
        'count' => $employees->count(),
        'data' => $employees->take(5)->values()
    ]);
});

Route::get('/test/employees-direct', function () {
    try {
        $response = Http::withoutVerifying()->withHeaders([
            'X-API-Key' => config('services.employees.api_key'),
        ])->get(config('services.employees.api_url'));

        return response()->json([
            'status' => $response->status(),
            'success' => $response->successful(),
            'body' => $response->json(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test/projects', function (ProjectService $service) {
    $projects = $service->getProjects();
    return response()->json([
        'count' => $projects->count(),
        'data' => $projects->take(5)->values()
    ]);
});

Route::get('/test/projects-direct', function () {
    try {
        $response = Http::withoutVerifying()->withHeaders([
            'X-API-Key' => config('services.projects.api_key'),
        ])->get(config('services.projects.api_url'));

        return response()->json([
            'status' => $response->status(),
            'success' => $response->successful(),
            'body' => $response->json(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test/opportunities', function (SalesforceService $service) {
    try {
        $opportunities = $service->getOpportunities();
        return response()->json([
            'count' => $opportunities->count(),
            'data' => $opportunities->take(5)->values()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test/clear-cache', function () {
    Cache::forget('employees_list');
    Cache::forget('projects_list');
    Cache::forget('salesforce_token');
    return response()->json(['message' => 'Cache cleared']);
});
