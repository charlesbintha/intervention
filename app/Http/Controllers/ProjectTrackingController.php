<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectTrackingRequest;
use App\Http\Requests\UpdateProjectTrackingRequest;
use App\Models\PlanRevision;
use App\Models\ProjectTracking;
use App\Services\EmployeeService;
use App\Services\ProjectService;
use App\Services\SalesforceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectTrackingController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly EmployeeService $employeeService,
        private readonly SalesforceService $salesforceService,
    ) {}

    public function index(): View
    {
        $query = ProjectTracking::query()
            ->with('activities')
            ->withCount(['activities', 'workLogs'])
            ->latest();

        $projectTrackings = $query->paginate(12);

        return view('project_trackings.index', compact('projectTrackings'));
    }

    public function create(): View
    {
        $projects = $this->projectService->getTrackableProjects();

        return view('project_trackings.create', compact('projects'));
    }

    public function store(StoreProjectTrackingRequest $request): RedirectResponse
    {
        $project = $this->projectService->getTrackableProjectByCode($request->string('external_project_code')->toString());

        if (! $project) {
            throw ValidationException::withMessages([
                'external_project_code' => 'Ce projet n’est plus disponible pour le suivi des travaux.',
            ]);
        }

        if (! $project['subsidiary']) {
            throw ValidationException::withMessages([
                'external_project_code' => 'La filiale exécutante de ce projet n’est pas reconnue.',
            ]);
        }

        $clientName = $project['client_name'] ?? null;

        if (! $clientName && ! empty($project['opportunity_id'])) {
            $clientName = $this->salesforceService->getOpportunityById($project['opportunity_id'])['account_name'] ?? null;
        }

        $projectTracking = ProjectTracking::create([
            ...$request->validated(),
            'external_project_code' => $project['code_projet'],
            'external_project_name' => $project['nom_projet'],
            'external_opportunity_id' => $project['opportunity_id'] ?: null,
            'subsidiary' => $project['subsidiary'],
            'client_name' => $clientName ?: $request->string('client_name')->toString() ?: null,
            'user_id' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('project-trackings.show', $projectTracking)
            ->with('success', 'Le suivi de travaux a été créé. Vous pouvez maintenant définir les activités du planning.');
    }

    public function show(ProjectTracking $projectTracking): View
    {
        Gate::authorize('view', $projectTracking);

        $projectTracking->load([
            'user',
            'activities.workLogs',
            'workLogs' => fn ($query) => $query->with(['activity', 'user'])->latest('started_at'),
            'actions' => fn ($query) => $query->with('activity')->orderBy('due_date'),
            'revisions' => fn ($query) => $query->with(['activity', 'user'])->latest('version'),
        ]);

        $employees = Gate::allows('update', $projectTracking)
            ? $this->employeeService->getEmployees()
            : collect();

        return view('project_trackings.show', compact('projectTracking', 'employees'));
    }

    public function edit(ProjectTracking $projectTracking): View
    {
        Gate::authorize('update', $projectTracking);

        return view('project_trackings.edit', compact('projectTracking'));
    }

    public function update(UpdateProjectTrackingRequest $request, ProjectTracking $projectTracking): RedirectResponse
    {
        $data = $request->validated();
        if ($projectTracking->baseline_approved_at) {
            unset($data['current_start_date'], $data['current_end_date']);
        }

        $projectTracking->update($data);

        return redirect()->route('project-trackings.show', $projectTracking)
            ->with('success', 'Les informations du suivi ont été mises à jour.');
    }

    public function destroy(ProjectTracking $projectTracking): RedirectResponse
    {
        Gate::authorize('delete', $projectTracking);
        $projectTracking->delete();

        return redirect()->route('project-trackings.index')
            ->with('success', 'Le suivi de travaux a été supprimé.');
    }

    public function approveBaseline(ProjectTracking $projectTracking): RedirectResponse
    {
        Gate::authorize('update', $projectTracking);

        $activities = $projectTracking->activities()->get();
        if ($activities->isEmpty()) {
            return back()->with('error', 'Ajoutez au moins une activité avant de valider le planning initial.');
        }

        DB::transaction(function () use ($projectTracking, $activities): void {
            foreach ($activities as $activity) {
                $activity->update([
                    'baseline_start_date' => $activity->current_start_date,
                    'baseline_end_date' => $activity->current_end_date,
                ]);
            }

            $startDate = $activities->min('current_start_date');
            $endDate = $activities->max('current_end_date');
            $projectTracking->update([
                'baseline_start_date' => $startDate,
                'baseline_end_date' => $endDate,
                'current_start_date' => $startDate,
                'current_end_date' => $endDate,
                'baseline_approved_at' => now(),
                'status' => 'active',
            ]);

            PlanRevision::create([
                'project_tracking_id' => $projectTracking->id,
                'user_id' => auth()->id(),
                'version' => 1,
                'reason' => 'Validation du planning initial',
                'new_values' => ['activity_ids' => $activities->pluck('id')->all()],
            ]);
        });

        return back()->with('success', 'Le planning initial a été validé et enregistré comme baseline V1.');
    }
}
