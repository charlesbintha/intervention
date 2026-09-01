<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectActionRequest;
use App\Models\ProjectAction;
use App\Models\ProjectTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectActionController extends Controller
{
    public function store(StoreProjectActionRequest $request, ProjectTracking $projectTracking): RedirectResponse
    {
        if ($request->filled('project_activity_id')) {
            $projectTracking->activities()->findOrFail($request->integer('project_activity_id'));
        }

        $validated = $request->validated();

        $projectTracking->actions()->create([
            ...$validated,
            'responsible_name' => $validated['responsible_names'][0],
            'user_id' => auth()->id(),
            'status' => 'open',
        ]);

        return back()->with('success', 'L’action a été ajoutée au suivi.');
    }

    public function updateStatus(Request $request, ProjectAction $projectAction): RedirectResponse
    {
        Gate::authorize('update', $projectAction->projectTracking);
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,completed,cancelled'],
            'completion_comment' => ['nullable', 'string'],
        ]);

        $projectAction->update([
            ...$validated,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Le statut de l’action a été mis à jour.');
    }

    public function destroy(ProjectAction $projectAction): RedirectResponse
    {
        Gate::authorize('update', $projectAction->projectTracking);

        $projectAction->delete();

        return back()->with('success', 'L’action a été supprimée.');
    }
}
