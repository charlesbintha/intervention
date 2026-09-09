<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkLogRequest;
use App\Http\Requests\UpdateWorkLogRequest;
use App\Models\ProjectActivity;
use App\Models\ProjectTracking;
use App\Models\WorkLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkLogController extends Controller
{
    public function store(StoreWorkLogRequest $request, ProjectTracking $projectTracking): RedirectResponse
    {
        $activity = $projectTracking->activities()->findOrFail($request->integer('project_activity_id'));
        $newTotal = (float) $activity->completed_quantity + (float) $request->input('quantity_completed');

        if ($newTotal > (float) $activity->planned_quantity) {
            return back()->withInput()->with('error', 'La quantité cumulée ne peut pas dépasser la quantité prévue. Replanifiez d’abord l’activité si le périmètre a augmenté.');
        }

        $validated = $request->validated();
        $startedAt = Carbon::parse($validated['started_at']);
        $endedAt = Carbon::parse($validated['ended_at']);

        DB::transaction(function () use ($validated, $projectTracking, $activity, $startedAt, $endedAt): void {
            $projectTracking->workLogs()->create([
                ...$validated,
                'work_date' => $startedAt->toDateString(),
                'start_time' => $startedAt->format('H:i:s'),
                'end_time' => $endedAt->format('H:i:s'),
                'user_id' => auth()->id(),
            ]);

            $this->refreshActivityProgress($activity);
        });

        return back()->with('success', 'Le travail réalisé a été enregistré et l’avancement recalculé.');
    }

    public function destroy(ProjectTracking $projectTracking, WorkLog $workLog): RedirectResponse
    {
        Gate::authorize('update', $projectTracking);
        abort_unless($workLog->project_tracking_id === $projectTracking->id, 404);

        $activity = $workLog->activity;
        DB::transaction(function () use ($workLog, $activity): void {
            $workLog->delete();
            $this->refreshActivityProgress($activity);
        });

        return back()->with('success', 'La déclaration a été supprimée et l’avancement recalculé.');
    }

    public function edit(ProjectTracking $projectTracking, WorkLog $workLog): View
    {
        Gate::authorize('update', $projectTracking);
        abort_unless($workLog->project_tracking_id === $projectTracking->id, 404);

        $projectTracking->load('activities');
        $startedAt = $workLog->started_at
            ?? Carbon::parse($workLog->work_date->format('Y-m-d').' '.($workLog->start_time ?? '00:00:00'));
        $endedAt = $workLog->ended_at
            ?? Carbon::parse($workLog->work_date->format('Y-m-d').' '.($workLog->end_time ?? '00:00:00'));

        return view('project_trackings.work_logs.edit', compact('projectTracking', 'workLog', 'startedAt', 'endedAt'));
    }

    public function update(UpdateWorkLogRequest $request, ProjectTracking $projectTracking, WorkLog $workLog): RedirectResponse
    {
        abort_unless($workLog->project_tracking_id === $projectTracking->id, 404);

        $activity = $projectTracking->activities()->findOrFail($request->integer('project_activity_id'));
        $otherCompletedQuantity = (float) $activity->workLogs()
            ->where('id', '!=', $workLog->id)
            ->sum('quantity_completed');
        $newTotal = $otherCompletedQuantity + (float) $request->input('quantity_completed');

        if ($newTotal > (float) $activity->planned_quantity) {
            return back()->withInput()->with('error', 'La quantité cumulée ne peut pas dépasser la quantité prévue. Replanifiez d’abord l’activité si le périmètre a augmenté.');
        }

        $validated = $request->validated();
        $startedAt = Carbon::parse($validated['started_at']);
        $endedAt = Carbon::parse($validated['ended_at']);
        $previousActivity = $workLog->activity;

        DB::transaction(function () use ($validated, $workLog, $activity, $previousActivity, $startedAt, $endedAt): void {
            $workLog->update([
                ...$validated,
                'work_date' => $startedAt->toDateString(),
                'start_time' => $startedAt->format('H:i:s'),
                'end_time' => $endedAt->format('H:i:s'),
            ]);

            if ($previousActivity->isNot($activity)) {
                $this->refreshActivityProgress($previousActivity);
            }

            $this->refreshActivityProgress($activity);
        });

        return redirect()->route('project-trackings.show', $projectTracking)
            ->with('success', 'Le travail réalisé a été modifié et l’avancement recalculé.');
    }

    private function refreshActivityProgress(ProjectActivity $activity): void
    {
        $completedQuantity = (float) $activity->workLogs()->sum('quantity_completed');
        $status = match (true) {
            $completedQuantity >= (float) $activity->planned_quantity => 'completed',
            $completedQuantity > 0 => 'in_progress',
            default => 'not_started',
        };

        $activity->update([
            'completed_quantity' => $completedQuantity,
            'status' => $status,
        ]);
    }
}
