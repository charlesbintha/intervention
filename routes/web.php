<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterventionUteController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectActionController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\ProjectBlockerController;
use App\Http\Controllers\ProjectTrackingController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification (publiques)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes protégées par authentification et compte actif
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Routes de profil
    Route::get('/profile/signature', [ProfileController::class, 'editSignature'])->name('profile.signature.edit');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');

    // Endpoint pour récupérer les détails d'une opportunité
    Route::get('/get-opportunity/{id}', function (App\Services\SalesforceService $salesforce, $id) {
        \Log::info('Fetching opportunity', ['id' => $id]);
        $opportunity = $salesforce->getOpportunityById($id);
        \Log::info('Opportunity result', ['opportunity' => $opportunity]);
        if ($opportunity) {
            return response()->json($opportunity);
        }
        \Log::warning('Opportunity not found', ['id' => $id]);

        return response()->json(['error' => 'Opportunity not found'], 404);
    })->name('get-opportunity');

    Route::prefix('surveys')->name('surveys.')->group(function () {
        Route::get('/', [SurveyController::class, 'index'])->name('index');
        Route::get('/create', [SurveyController::class, 'create'])->name('create');
        Route::post('/', [SurveyController::class, 'store'])->name('store');
        Route::get('/{survey}', [SurveyController::class, 'show'])->name('show');
        Route::get('/{survey}/edit', [SurveyController::class, 'edit'])->name('edit');
        Route::put('/{survey}', [SurveyController::class, 'update'])->name('update');
        Route::delete('/{survey}', [SurveyController::class, 'destroy'])->name('destroy');
        Route::get('/{survey}/layout', [SurveyController::class, 'layout'])->name('layout');
        Route::post('/{survey}/layout', [SurveyController::class, 'storeLayout'])->name('layout.store');
        Route::get('/{survey}/pdf', [SurveyController::class, 'generatePDF'])->name('pdf');
        Route::post('/{survey}/validate', [SurveyController::class, 'validate'])->name('validate');
        Route::post('/{survey}/intervenants', [SurveyController::class, 'updateIntervenants'])->name('intervenants.update');
    });

    Route::prefix('maintenances')->name('maintenances.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::get('/{maintenance}', [MaintenanceController::class, 'show'])->name('show');
        Route::get('/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('edit');
        Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('update');
        Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('destroy');
        Route::get('/{maintenance}/layout', [MaintenanceController::class, 'layout'])->name('layout');
        Route::post('/{maintenance}/layout', [MaintenanceController::class, 'storeLayout'])->name('layout.store');
        Route::get('/{maintenance}/pdf', [MaintenanceController::class, 'generatePDF'])->name('pdf');
        Route::post('/{maintenance}/validate', [MaintenanceController::class, 'validate'])->name('validate');
        Route::post('/{maintenance}/intervenants', [MaintenanceController::class, 'updateIntervenants'])->name('intervenants.update');
    });

    Route::prefix('intervention-utes')->name('intervention-utes.')->group(function () {
        Route::get('/', [InterventionUteController::class, 'index'])->name('index');
        Route::get('/create', [InterventionUteController::class, 'create'])->name('create');
        Route::post('/', [InterventionUteController::class, 'store'])->name('store');
        Route::get('/{interventionUte}', [InterventionUteController::class, 'show'])->name('show');
        Route::get('/{interventionUte}/edit', [InterventionUteController::class, 'edit'])->name('edit');
        Route::put('/{interventionUte}', [InterventionUteController::class, 'update'])->name('update');
        Route::delete('/{interventionUte}', [InterventionUteController::class, 'destroy'])->name('destroy');
        Route::get('/{interventionUte}/pdf', [InterventionUteController::class, 'generatePDF'])->name('pdf');
        Route::post('/{interventionUte}/validate', [InterventionUteController::class, 'validate'])->name('validate');
        Route::post('/{interventionUte}/intervenants', [InterventionUteController::class, 'updateIntervenants'])->name('intervenants.update');
    });

    Route::resource('project-trackings', ProjectTrackingController::class)
        ->parameters(['project-trackings' => 'projectTracking']);
    Route::post('project-trackings/{projectTracking}/approve-baseline', [ProjectTrackingController::class, 'approveBaseline'])
        ->name('project-trackings.approve-baseline');
    Route::post('project-trackings/{projectTracking}/activities', [ProjectActivityController::class, 'store'])
        ->name('project-trackings.activities.store');
    Route::get('project-activities/{activity}/edit', [ProjectActivityController::class, 'edit'])
        ->name('project-activities.edit');
    Route::put('project-activities/{activity}', [ProjectActivityController::class, 'update'])
        ->name('project-activities.update');
    Route::delete('project-activities/{activity}', [ProjectActivityController::class, 'destroy'])
        ->name('project-activities.destroy');
    Route::post('project-trackings/{projectTracking}/work-logs', [WorkLogController::class, 'store'])
        ->name('project-trackings.work-logs.store');
    Route::delete('project-trackings/{projectTracking}/work-logs/{workLog}', [WorkLogController::class, 'destroy'])
        ->name('project-trackings.work-logs.destroy');
    Route::post('project-trackings/{projectTracking}/blockers', [ProjectBlockerController::class, 'store'])
        ->name('project-trackings.blockers.store');
    Route::patch('project-blockers/{blocker}/status', [ProjectBlockerController::class, 'updateStatus'])
        ->name('project-blockers.status');
    Route::delete('project-blockers/{blocker}', [ProjectBlockerController::class, 'destroy'])
        ->name('project-blockers.destroy');
    Route::post('project-trackings/{projectTracking}/actions', [ProjectActionController::class, 'store'])
        ->name('project-trackings.actions.store');
    Route::patch('project-actions/{projectAction}/status', [ProjectActionController::class, 'updateStatus'])
        ->name('project-actions.status');
    Route::delete('project-actions/{projectAction}', [ProjectActionController::class, 'destroy'])
        ->name('project-actions.destroy');

    // Route pour servir les fichiers attachés
    Route::get('/attachments/{path}', function ($path) {
        $filePath = storage_path('app/public/attachments/'.$path);

        if (! file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    })->where('path', '.*')->name('attachments.show');

    // Routes d'administration (réservées aux admins)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/regenerate-password', [UserController::class, 'regeneratePassword'])->name('users.regenerate-password');
    });
});
