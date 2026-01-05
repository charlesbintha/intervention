<?php

use App\Http\Controllers\Api\AuthController;
use App\Services\EmployeeService;
use App\Services\ProjectService;
use App\Services\SalesforceService;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Get data for forms
    Route::get('/opportunities', function (SalesforceService $salesforce) {
        return response()->json($salesforce->getOpportunities());
    });

    Route::get('/projects', function (ProjectService $projectService) {
        return response()->json($projectService->getProjects());
    });

    Route::get('/employees', function (EmployeeService $employeeService) {
        return response()->json($employeeService->getEmployees());
    });

    Route::get('/opportunities/{id}', function (SalesforceService $salesforce, $id) {
        $opportunity = $salesforce->getOpportunityById($id);
        if ($opportunity) {
            return response()->json($opportunity);
        }

        return response()->json(['error' => 'Opportunity not found'], 404);
    });

    Route::get('/accounts', function (SalesforceService $salesforce) {
        return response()->json($salesforce->getAccounts());
    });

    // Filiales (subsidiaries)
    Route::get('/subsidiaries', function () {
        return response()->json([
            ['code' => 'GUT', 'name' => 'Groupe Univers Telecom'],
            ['code' => 'CP', 'name' => 'Cabinet Pencco'],
            ['code' => 'UTA', 'name' => 'Univers Telecom Afrique'],
            ['code' => 'UA', 'name' => 'Univers Academy'],
            ['code' => 'UTE', 'name' => 'Univers Technology & Energy'],
            ['code' => 'UC', 'name' => 'Univers Capital'],
        ]);
    });

    // Surveys API
    Route::apiResource('surveys', \App\Http\Controllers\SurveyController::class)->names([
        'index' => 'api.surveys.index',
        'store' => 'api.surveys.store',
        'show' => 'api.surveys.show',
        'update' => 'api.surveys.update',
        'destroy' => 'api.surveys.destroy',
    ]);
    Route::get('/surveys/{survey}/pdf', [\App\Http\Controllers\SurveyController::class, 'generatePDF'])->name('api.surveys.pdf');
    Route::post('/surveys/{survey}/signature', [\App\Http\Controllers\SurveyController::class, 'storeSignature'])->name('api.surveys.signature');
    Route::post('/surveys/{survey}/intervenants', [\App\Http\Controllers\SurveyController::class, 'updateIntervenants'])->name('api.surveys.intervenants');
    Route::post('/surveys/{survey}/validate', [\App\Http\Controllers\SurveyController::class, 'validate'])->name('api.surveys.validate');

    // Maintenances API
    Route::apiResource('maintenances', \App\Http\Controllers\MaintenanceController::class)->names([
        'index' => 'api.maintenances.index',
        'store' => 'api.maintenances.store',
        'show' => 'api.maintenances.show',
        'update' => 'api.maintenances.update',
        'destroy' => 'api.maintenances.destroy',
    ]);
    Route::get('/maintenances/{maintenance}/pdf', [\App\Http\Controllers\MaintenanceController::class, 'generatePDF'])->name('api.maintenances.pdf');
    Route::post('/maintenances/{maintenance}/signature', [\App\Http\Controllers\MaintenanceController::class, 'storeSignature'])->name('api.maintenances.signature');
    Route::post('/maintenances/{maintenance}/intervenants', [\App\Http\Controllers\MaintenanceController::class, 'updateIntervenants'])->name('api.maintenances.intervenants');
    Route::post('/maintenances/{maintenance}/validate', [\App\Http\Controllers\MaintenanceController::class, 'validate'])->name('api.maintenances.validate');

    // Intervention UTE API
    Route::apiResource('intervention-utes', \App\Http\Controllers\InterventionUteController::class)->names([
        'index' => 'api.intervention-utes.index',
        'store' => 'api.intervention-utes.store',
        'show' => 'api.intervention-utes.show',
        'update' => 'api.intervention-utes.update',
        'destroy' => 'api.intervention-utes.destroy',
    ]);
    Route::get('/intervention-utes/{interventionUte}/pdf', [\App\Http\Controllers\InterventionUteController::class, 'generatePDF'])->name('api.intervention-utes.pdf');
    Route::post('/intervention-utes/{interventionUte}/signature', [\App\Http\Controllers\InterventionUteController::class, 'storeSignature'])->name('api.intervention-utes.signature');
    Route::post('/intervention-utes/{interventionUte}/intervenants', [\App\Http\Controllers\InterventionUteController::class, 'updateIntervenants'])->name('api.intervention-utes.intervenants');
    Route::post('/intervention-utes/{interventionUte}/validate', [\App\Http\Controllers\InterventionUteController::class, 'validate'])->name('api.intervention-utes.validate');
});
