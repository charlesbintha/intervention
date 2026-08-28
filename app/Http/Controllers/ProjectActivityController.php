<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectActivityRequest;
use App\Http\Requests\UpdateProjectActivityRequest;
use App\Models\PlanRevision;
use App\Models\ProjectActivity;
use App\Models\ProjectTracking;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectActivityController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function store(StoreProjectActivityRequest $request, ProjectTracking $projectTracking): RedirectResponse
    {
        $activity = $projectTracking->activities()->create([
            ...$this->prepareData($request->validated()),
            'sort_order' => ((int) $projectTracking->activities()->max('sort_order')) + 1,
        ]);

        if ($projectTracking->baseline_approved_at) {
            $this->recordRevision($projectTracking, $activity, $request->string('change_reason')->toString(), null, $activity->toArray());
        }

        $this->syncCurrentSchedule($projectTracking);

        return back()->with('success', 'L’activité a été ajoutée au planning.');
    }

    public function edit(ProjectActivity $activity): View
    {
        Gate::authorize('update', $activity->projectTracking);
        $employees = $this->employeeService->getEmployees();

        return view('project_trackings.activities.edit', compact('activity', 'employees'));
    }

    public function update(UpdateProjectActivityRequest $request, ProjectActivity $activity): RedirectResponse
    {
        $oldValues = $activity->only($this->revisionFields());
        $activity->update($this->prepareData($request->validated()));

        if ($activity->projectTracking->baseline_approved_at) {
            $this->recordRevision(
                $activity->projectTracking,
                $activity,
                $request->string('change_reason')->toString(),
                $oldValues,
                $activity->only($this->revisionFields()),
            );
        }

        $this->syncCurrentSchedule($activity->projectTracking);

        return redirect()->route('project-trackings.show', $activity->projectTracking)
            ->with('success', 'L’activité et le planning courant ont été mis à jour.');
    }

    public function destroy(Request $request, ProjectActivity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity->projectTracking);

        if ($activity->projectTracking->baseline_approved_at) {
            $validated = $request->validate(['change_reason' => ['required', 'string', 'min:5']]);
            $this->recordRevision($activity->projectTracking, $activity, $validated['change_reason'], $activity->toArray(), null);
        }

        $tracking = $activity->projectTracking;
        $activity->delete();
        $this->syncCurrentSchedule($tracking);

        return redirect()->route('project-trackings.show', $tracking)
            ->with('success', 'L’activité a été supprimée du planning courant.');
    }

    private function prepareData(array $data): array
    {
        $agents = collect($data['assigned_agents'] ?? [])
            ->map(fn (string $agent): string => trim($agent))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $externalStakeholders = collect($data['external_stakeholders'] ?? [])
            ->map(fn (array $stakeholder): array => [
                'last_name' => trim((string) ($stakeholder['last_name'] ?? '')),
                'first_name' => trim((string) ($stakeholder['first_name'] ?? '')),
                'email' => filled($stakeholder['email'] ?? null) ? trim((string) $stakeholder['email']) : null,
            ])
            ->filter(fn (array $stakeholder): bool => $stakeholder['last_name'] !== '' || $stakeholder['first_name'] !== '')
            ->values()
            ->all();

        return [
            ...Arr::except($data, ['assigned_agents', 'external_stakeholders', 'change_reason']),
            'assigned_agents' => $agents,
            'external_stakeholders' => $externalStakeholders,
            'unit' => 'pourcentage',
        ];
    }

    private function recordRevision(ProjectTracking $tracking, ProjectActivity $activity, string $reason, ?array $oldValues, ?array $newValues): void
    {
        PlanRevision::create([
            'project_tracking_id' => $tracking->id,
            'project_activity_id' => $activity->id,
            'user_id' => auth()->id(),
            'version' => ((int) $tracking->revisions()->max('version')) + 1,
            'reason' => $reason,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    private function revisionFields(): array
    {
        return ['lot_name', 'phase_name', 'name', 'current_start_date', 'current_end_date', 'planned_quantity', 'status', 'assigned_agents', 'external_stakeholders'];
    }

    private function syncCurrentSchedule(ProjectTracking $tracking): void
    {
        $tracking->update([
            'current_start_date' => $tracking->activities()->min('current_start_date'),
            'current_end_date' => $tracking->activities()->max('current_end_date'),
        ]);
    }
}
