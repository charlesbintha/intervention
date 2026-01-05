<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Services\EmployeeService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    protected $projectService;

    protected $employeeService;

    public function __construct(ProjectService $projectService, EmployeeService $employeeService)
    {
        $this->projectService = $projectService;
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        // Admin can see all, regular users only see their own
        $query = Maintenance::with('intervenants', 'signature', 'user');

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $maintenances = $query->latest()->get();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($maintenances);
        }

        // Web request - return view
        return view('maintenances.index', compact('maintenances'));
    }

    public function create()
    {
        $projects = $this->projectService->getProjects();
        $employees = $this->employeeService->getEmployees();

        return view('maintenances.create', compact('projects', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subsidiary' => 'required|in:GUT,CP,UTA,UA,UTE,UC',
            'project_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_function' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
            'nature_intervention' => 'nullable|array',
            'type_intervention' => 'nullable|array',
            'layout_content' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,validated',
            'intervenants_gut' => 'nullable|array',
            'intervenants_gut.*.nom' => 'required|string',
            'intervenants_gut.*.prenom' => 'required|string',
            'intervenants_gut.*.email' => 'nullable|email',
            'intervenants_gut.*.telephone' => 'nullable|string',
            'intervenants_rencontres' => 'nullable|array',
            'intervenants_rencontres.*.nom' => 'required|string',
            'intervenants_rencontres.*.prenom' => 'required|string',
            'intervenants_rencontres.*.email' => 'nullable|email',
            'intervenants_rencontres.*.telephone' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        // Convertir les tableaux en chaînes séparées par des virgules
        if (isset($validated['nature_intervention'])) {
            $validated['nature_intervention'] = implode(', ', $validated['nature_intervention']);
        }
        if (isset($validated['type_intervention'])) {
            $validated['type_intervention'] = implode(', ', $validated['type_intervention']);
        }

        $maintenance = null;

        DB::transaction(function () use ($validated, $request, &$maintenance) {
            $maintenance = Maintenance::create([
                ...$validated,
                'user_id' => auth()->id(),
            ]);

            if ($request->has('intervenants_gut')) {
                foreach ($request->intervenants_gut as $intervenant) {
                    $maintenance->intervenants()->create([
                        'type' => 'gut',
                        'source' => $intervenant['source'] ?? 'manual',
                        'nom' => $intervenant['nom'],
                        'prenom' => $intervenant['prenom'],
                        'email' => $intervenant['email'] ?? null,
                        'telephone' => $intervenant['telephone'] ?? null,
                        'api_id' => $intervenant['api_id'] ?? null,
                    ]);
                }
            }

            if ($request->has('intervenants_rencontres')) {
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $maintenance->intervenants()->create([
                        'type' => 'rencontre',
                        'source' => 'manual',
                        'nom' => $intervenant['nom'],
                        'prenom' => $intervenant['prenom'],
                        'email' => $intervenant['email'] ?? null,
                        'telephone' => $intervenant['telephone'] ?? null,
                    ]);
                }
            }
        });

        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $file) {
                $path = $file->store('attachments', 'public');
                $maintenance->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Maintenance créée avec succès',
                'data' => $maintenance,
            ], 201);
        }

        // Web request - return redirect
        session()->flash('maintenance_id', $maintenance->id);

        return redirect()->route('maintenances.layout', $maintenance->id)
            ->with('success', 'Maintenance créée avec succès. Veuillez compléter la mise en page.');
    }

    public function show(Request $request, $maintenance)
    {
        $id = (int) $maintenance;

        $query = Maintenance::with('intervenants', 'signature', 'user')
            ->whereKey($id);

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $record = $query->firstOrFail();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($record);
        }

        return view('maintenances.show', ['maintenance' => $record]);
    }

    public function edit(Maintenance $maintenance)
    {
        // Check authorization: admin can edit all, regular users only their own
        if (! auth()->user()->isAdmin() && (int) $maintenance->user_id !== (int) auth()->id()) {
            abort(403);
        }

        // Check if already validated - prevent editing
        if ($maintenance->status === 'validated') {
            abort(403, 'Cette maintenance validée ne peut pas être modifiée.');
        }

        $maintenance->load('intervenants');
        $employees = $this->employeeService->getEmployees();

        return view('maintenances.edit', compact('maintenance', 'employees'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        // Check authorization: admin can update all, regular users only their own
        if (! auth()->user()->isAdmin() && $maintenance->user_id !== auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if already validated - prevent updates
        if ($maintenance->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette maintenance validée ne peut pas être modifiée.'], 403);
            }

            abort(403, 'Cette maintenance validée ne peut pas être modifiée.');
        }

        $validated = $request->validate([
            'subsidiary' => 'nullable|in:GUT,CP,UTA,UA,UTE,UC',
            'project_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_function' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
            'nature_intervention' => 'nullable|array',
            'type_intervention' => 'nullable|array',
            'layout_content' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,validated',
            'intervenants_gut' => 'nullable|array',
            'intervenants_gut.*.nom' => 'required|string',
            'intervenants_gut.*.prenom' => 'required|string',
            'intervenants_gut.*.email' => 'nullable|email',
            'intervenants_gut.*.telephone' => 'nullable|string',
            'intervenants_rencontres' => 'nullable|array',
            'intervenants_rencontres.*.nom' => 'required|string',
            'intervenants_rencontres.*.prenom' => 'required|string',
            'intervenants_rencontres.*.email' => 'nullable|email',
            'intervenants_rencontres.*.telephone' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        // Convertir les tableaux en chaînes séparées par des virgules
        if (isset($validated['nature_intervention'])) {
            $validated['nature_intervention'] = implode(', ', $validated['nature_intervention']);
        }
        if (isset($validated['type_intervention'])) {
            $validated['type_intervention'] = implode(', ', $validated['type_intervention']);
        }

        DB::transaction(function () use ($validated, $request, $maintenance) {
            $maintenance->update($validated);

            // Update intervenants if provided
            if ($request->has('intervenants_gut')) {
                // Delete existing GUT intervenants
                $maintenance->intervenantsGut()->delete();

                // Create new GUT intervenants
                foreach ($request->intervenants_gut as $intervenant) {
                    $maintenance->intervenants()->create([
                        'type' => 'gut',
                        'source' => $intervenant['source'] ?? 'manual',
                        'nom' => $intervenant['nom'],
                        'prenom' => $intervenant['prenom'],
                        'email' => $intervenant['email'] ?? null,
                        'telephone' => $intervenant['telephone'] ?? null,
                        'api_id' => $intervenant['api_id'] ?? null,
                    ]);
                }
            }

            if ($request->has('intervenants_rencontres')) {
                // Delete existing rencontres intervenants
                $maintenance->intervenantsRencontres()->delete();

                // Create new rencontres intervenants
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $maintenance->intervenants()->create([
                        'type' => 'rencontre',
                        'source' => 'manual',
                        'nom' => $intervenant['nom'],
                        'prenom' => $intervenant['prenom'],
                        'email' => $intervenant['email'] ?? null,
                        'telephone' => $intervenant['telephone'] ?? null,
                    ]);
                }
            }
        });

        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $file) {
                $path = $file->store('attachments', 'public');
                $maintenance->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $maintenance->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Maintenance mise à jour avec succès',
                'data' => $maintenance,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Maintenance mise à jour avec succès.');
    }

    public function destroy(Request $request, Maintenance $maintenance)
    {
        // Check authorization: admin can delete all, regular users only their own
        if (! auth()->user()->isAdmin() && $maintenance->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if already validated - prevent deletion
        if ($maintenance->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette maintenance validée ne peut pas être supprimée.'], 403);
            }

            abort(403, 'Cette maintenance validée ne peut pas être supprimée.');
        }

        $maintenance->delete();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Maintenance supprimée avec succès',
            ]);
        }

        // Web request - return redirect
        return redirect()->route('maintenances.index')
            ->with('success', 'Maintenance supprimée avec succès.');
    }

    public function layout(Maintenance $maintenance)
    {
        return view('maintenances.layout', compact('maintenance'));
    }

    public function storeLayout(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'layout_content' => 'required|string',
        ]);

        $maintenance->update([
            'layout_content' => $validated['layout_content'],
            'status' => 'pending',
        ]);

        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Mise en page enregistrée avec succès.');
    }

    public function generatePDF(Request $request, Maintenance $maintenance)
    {
        // Check authorization: admin can see all, regular users only their own
        if (! auth()->user()->isAdmin() && $maintenance->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        $maintenance->load('intervenants', 'signature', 'user');

        // API request - return JSON with PDF URL or generate PDF
        if ($request->expectsJson() || $request->is('api/*')) {
            // For API, we return the view HTML as JSON or redirect to PDF
            // You might want to use a PDF generation library like DomPDF or Snappy
            // For now, we'll return a message indicating PDF generation
            return response()->json([
                'message' => 'PDF generation endpoint',
                'maintenance_id' => $maintenance->id,
                'note' => 'Use the web interface to generate PDF or implement PDF generation library',
            ]);
        }

        // Web request - return view
        return view('maintenances.pdf', compact('maintenance'));
    }

    public function storeSignature(Request $request, Maintenance $maintenance)
    {
        // Check authorization: admin can sign all, regular users only their own
        if (! auth()->user()->isAdmin() && $maintenance->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        $validated = $request->validate([
            'signature_data' => 'required|string',
            'signer_name' => 'required|string|max:255',
        ]);

        $signature = $maintenance->signature()->updateOrCreate(
            ['signable_id' => $maintenance->id, 'signable_type' => Maintenance::class],
            [
                'signature_data' => $validated['signature_data'],
                'signer_name' => $validated['signer_name'],
                'signed_at' => now(),
            ]
        );

        // Update status to validated after signature
        $maintenance->update(['status' => 'validated']);

        $maintenance->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Signature enregistrée avec succès',
                'data' => $maintenance,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Signature enregistrée avec succès.');
    }

    public function validate(Request $request, Maintenance $maintenance): mixed
    {
        // Authorization: Owner only (not admin) can validate their own maintenances
        if ($maintenance->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403, 'Vous n\'êtes pas autorisé à valider cette maintenance.');
        }

        // Check if already validated - prevent double validation
        if ($maintenance->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette maintenance est déjà validée.'], 409);
            }

            return redirect()->route('maintenances.show', $maintenance)
                ->with('info', 'Cette maintenance est déjà validée.');
        }

        // Update status to validated
        $maintenance->update(['status' => 'validated']);

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Maintenance validée avec succès',
                'data' => $maintenance,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Maintenance validée avec succès.');
    }

    public function updateIntervenants(Request $request, Maintenance $maintenance): mixed
    {
        // Authorization: Only owner can update intervenants
        if ($maintenance->user_id != auth()->id() && ! auth()->user()->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if maintenance is already validated
        if ($maintenance->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette maintenance validée ne peut pas être modifiée.'], 403);
            }

            abort(403, 'Cette maintenance validée ne peut pas être modifiée.');
        }

        $validated = $request->validate([
            'intervenants_gut' => 'nullable|array',
            'intervenants_gut.*.nom' => 'required|string',
            'intervenants_gut.*.prenom' => 'required|string',
            'intervenants_gut.*.email' => 'nullable|email',
            'intervenants_gut.*.telephone' => 'nullable|string',
            'intervenants_rencontres' => 'nullable|array',
            'intervenants_rencontres.*.nom' => 'required|string',
            'intervenants_rencontres.*.prenom' => 'required|string',
            'intervenants_rencontres.*.email' => 'nullable|email',
            'intervenants_rencontres.*.telephone' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $maintenance) {
            // Update GUT intervenants if provided
            if ($request->has('intervenants_gut')) {
                // Delete existing GUT intervenants
                $maintenance->intervenantsGut()->delete();

                // Create new GUT intervenants
                foreach ($request->intervenants_gut as $intervenant) {
                    $maintenance->intervenants()->create([
                        'type' => 'gut',
                        'source' => $intervenant['source'] ?? 'manual',
                        'nom' => $intervenant['nom'],
                        'prenom' => $intervenant['prenom'],
                        'email' => $intervenant['email'] ?? null,
                        'telephone' => $intervenant['telephone'] ?? null,
                        'api_id' => $intervenant['api_id'] ?? null,
                    ]);
                }
            }

            // Update rencontres intervenants if provided
            if ($request->has('intervenants_rencontres')) {
                // Delete existing rencontres intervenants
                $maintenance->intervenantsRencontres()->delete();

                // Create new rencontres intervenants
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $maintenance->intervenants()->create([
                        'type' => 'rencontre',
                        'source' => 'manual',
                        'nom' => $intervenant['nom'],
                        'prenom' => $intervenant['prenom'],
                        'email' => $intervenant['email'] ?? null,
                        'telephone' => $intervenant['telephone'] ?? null,
                    ]);
                }
            }
        });

        $maintenance->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Intervenants mis à jour avec succès',
                'data' => $maintenance,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Intervenants mis à jour avec succès.');
    }
}
