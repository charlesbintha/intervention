<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectBlockerRequest;
use App\Models\ProjectBlocker;
use App\Models\ProjectTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectBlockerController extends Controller
{
    public function store(StoreProjectBlockerRequest $request, ProjectTracking $projectTracking): RedirectResponse
    {
        if ($request->filled('project_activity_id')) {
            $projectTracking->activities()->findOrFail($request->integer('project_activity_id'));
        }

        $projectTracking->blockers()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'status' => 'open',
        ]);

        return back()->with('success', 'Le blocage a été signalé.');
    }

    public function updateStatus(Request $request, ProjectBlocker $blocker): RedirectResponse
    {
        Gate::authorize('update', $blocker->projectTracking);
        $validated = $request->validate([
            'status' => ['required', 'in:open,analysing,processing,resolved,closed'],
            'proposed_solution' => ['nullable', 'string'],
        ]);

        $blocker->update([
            ...$validated,
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now()->toDateString() : null,
        ]);

        return back()->with('success', 'Le statut du blocage a été mis à jour.');
    }

    public function destroy(ProjectBlocker $blocker): RedirectResponse
    {
        Gate::authorize('update', $blocker->projectTracking);

        $blocker->delete();

        return back()->with('success', 'Le blocage a été supprimé.');
    }
}
