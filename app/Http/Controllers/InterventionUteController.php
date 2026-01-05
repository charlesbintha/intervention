<?php

namespace App\Http\Controllers;

use App\Models\InterventionUte;
use App\Services\EmployeeService;
use App\Services\SalesforceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InterventionUteController extends Controller
{
    protected $employeeService;

    protected $salesforceService;

    public function __construct(EmployeeService $employeeService, SalesforceService $salesforceService)
    {
        $this->employeeService = $employeeService;
        $this->salesforceService = $salesforceService;
    }

    public function index(Request $request)
    {
        // Admin can see all, regular users only see their own
        $query = InterventionUte::with('intervenants', 'signature', 'user');

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $interventions = $query->latest()->get();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($interventions);
        }

        // Web request - return view
        return view('intervention_utes.index', compact('interventions'));
    }

    public function create()
    {
        $employees = $this->employeeService->getEmployees();
        $accounts = $this->salesforceService->getAccounts();

        return view('intervention_utes.create', compact('employees', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subsidiary' => 'required|in:GUT,CP,UTA,UA,UTE,UC',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_function' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
            'diagnostic' => 'required|in:cablage,wifi,FAI,electricite,autre',
            'type' => 'required|in:changement_piece,entretien,depannage,autre',
            'observations' => 'nullable|string',
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

        $intervention = null;

        DB::transaction(function () use ($validated, $request, &$intervention) {
            $intervention = InterventionUte::create([
                ...$validated,
                'user_id' => auth()->id(),
            ]);

            if ($request->has('intervenants_gut')) {
                foreach ($request->intervenants_gut as $intervenant) {
                    $intervention->intervenants()->create([
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
                    $intervention->intervenants()->create([
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
                $intervention->attachments()->create([
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
                'message' => 'Intervention créées avec succès',
                'data' => $intervention,
            ], 201);
        }

        // Web request - return redirect
        session()->flash('intervention_id', $intervention->id);

        return redirect()->route('intervention-utes.show', $intervention->id)
            ->with('success', 'Intervention créées avec succès.');
    }

    public function show(Request $request, $interventionUte)
    {
        $id = (int) $interventionUte;

        $query = InterventionUte::with('intervenants', 'signature', 'user')
            ->whereKey($id);

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $record = $query->firstOrFail();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($record);
        }

        return view('intervention_utes.show', ['interventionUte' => $record]);
    }

    public function edit(InterventionUte $interventionUte)
    {
        // Check authorization: admin can edit all, regular users only their own
        if (! auth()->user()->isAdmin() && (int) $interventionUte->user_id !== (int) auth()->id()) {
            abort(403);
        }

        // Check if already validated - prevent editing
        if ($interventionUte->status === 'validated') {
            abort(403, 'Cette intervention validÃ©e ne peut pas Ãªtre modifiÃ©e.');
        }

        $interventionUte->load('intervenants');

        return view('intervention_utes.edit', compact('interventionUte'));
    }

    public function update(Request $request, InterventionUte $interventionUte)
    {
        // Check authorization: admin can update all, regular users only their own
        if (! auth()->user()->isAdmin() && $interventionUte->user_id !== auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if already validated - prevent updates
        if ($interventionUte->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette intervention validÃ©e ne peut pas Ãªtre modifiÃ©e.'], 403);
            }

            abort(403, 'Cette intervention validÃ©e ne peut pas Ãªtre modifiÃ©e.');
        }

        $validated = $request->validate([
            'subsidiary' => 'nullable|in:GUT,CP,UTA,UA,UTE,UC',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_function' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
            'diagnostic' => 'required|in:cablage,wifi,FAI,electricite,autre',
            'type' => 'required|in:changement_piece,entretien,depannage,autre',
            'observations' => 'nullable|string',
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

        DB::transaction(function () use ($validated, $request, $interventionUte) {
            $interventionUte->update($validated);

            // Update intervenants if provided
            if ($request->has('intervenants_gut')) {
                // Delete existing GUT intervenants
                $interventionUte->intervenantsGut()->delete();

                // Create new GUT intervenants
                foreach ($request->intervenants_gut as $intervenant) {
                    $interventionUte->intervenants()->create([
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
                $interventionUte->intervenantsRencontres()->delete();

                // Create new rencontres intervenants
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $interventionUte->intervenants()->create([
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
                $interventionUte->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $interventionUte->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Intervention mise Ã  jour avec succÃ¨s',
                'data' => $interventionUte,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('intervention-utes.show', $interventionUte)
            ->with('success', 'Intervention mise Ã  jour avec succÃ¨s.');
    }

    public function destroy(Request $request, InterventionUte $interventionUte)
    {
        // Check authorization: admin can delete all, regular users only their own
        if (! auth()->user()->isAdmin() && $interventionUte->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if already validated - prevent deletion
        if ($interventionUte->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette intervention validÃ©e ne peut pas Ãªtre supprimÃ©e.'], 403);
            }

            abort(403, 'Cette intervention validÃ©e ne peut pas Ãªtre supprimÃ©e.');
        }

        $interventionUte->delete();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Intervention supprimÃ©e avec succÃ¨s',
            ]);
        }

        // Web request - return redirect
        return redirect()->route('intervention-utes.index')
            ->with('success', 'Intervention supprimÃ©e avec succÃ¨s.');
    }

    public function generatePDF(Request $request, InterventionUte $interventionUte)
    {
        // Check authorization: admin can see all, regular users only their own
        if (! auth()->user()->isAdmin() && $interventionUte->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        $interventionUte->load('intervenants', 'signature', 'user');

        // API request - return JSON with PDF URL or generate PDF
        if ($request->expectsJson() || $request->is('api/*')) {
            // For API, we return the view HTML as JSON or redirect to PDF
            // You might want to use a PDF generation library like DomPDF or Snappy
            // For now, we'll return a message indicating PDF generation
            return response()->json([
                'message' => 'PDF generation endpoint',
                'intervention_ute_id' => $interventionUte->id,
                'note' => 'Use the web interface to generate PDF or implement PDF generation library',
            ]);
        }

        // Web request - return view
        return view('intervention_utes.pdf', compact('interventionUte'));
    }

    public function storeSignature(Request $request, InterventionUte $interventionUte)
    {
        // Check authorization: admin can sign all, regular users only their own
        if (! auth()->user()->isAdmin() && $interventionUte->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        $validated = $request->validate([
            'signature_data' => 'required|string',
            'signer_name' => 'required|string|max:255',
        ]);

        $signature = $interventionUte->signature()->updateOrCreate(
            ['signable_id' => $interventionUte->id, 'signable_type' => InterventionUte::class],
            [
                'signature_data' => $validated['signature_data'],
                'signer_name' => $validated['signer_name'],
                'signed_at' => now(),
            ]
        );

        // Update status to validated after signature
        $interventionUte->update(['status' => 'validated']);

        $interventionUte->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Signature enregistrÃ©e avec succÃ¨s',
                'data' => $interventionUte,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('intervention-utes.show', $interventionUte)
            ->with('success', 'Signature enregistrÃ©e avec succÃ¨s.');
    }

    public function validate(Request $request, InterventionUte $interventionUte): mixed
    {
        // Authorization: Owner only (not admin) can validate their own interventions
        if ($interventionUte->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403, 'Vous n\'Ãªtes pas autorisÃ© Ã  valider cette intervention.');
        }

        // Check if already validated - prevent double validation
        if ($interventionUte->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette intervention est dÃ©jÃ  validÃ©e.'], 409);
            }

            return redirect()->route('intervention-utes.show', $interventionUte)
                ->with('info', 'Cette intervention est dÃ©jÃ  validÃ©e.');
        }

        // Update status to validated
        $interventionUte->update(['status' => 'validated']);

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Intervention validÃ©e avec succÃ¨s',
                'data' => $interventionUte,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('intervention-utes.show', $interventionUte)
            ->with('success', 'Intervention validÃ©e avec succÃ¨s.');
    }

    public function updateIntervenants(Request $request, InterventionUte $interventionUte): mixed
    {
        // Authorization: Only owner can update intervenants
        if ($interventionUte->user_id != auth()->id() && ! auth()->user()->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if intervention is already validated
        if ($interventionUte->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Cette intervention validée ne peut pas être modifiée.'], 403);
            }

            abort(403, 'Cette intervention validée ne peut pas être modifiée.');
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

        DB::transaction(function () use ($request, $interventionUte) {
            // Update GUT intervenants if provided
            if ($request->has('intervenants_gut')) {
                // Delete existing GUT intervenants
                $interventionUte->intervenantsGut()->delete();

                // Create new GUT intervenants
                foreach ($request->intervenants_gut as $intervenant) {
                    $interventionUte->intervenants()->create([
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
                $interventionUte->intervenantsRencontres()->delete();

                // Create new rencontres intervenants
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $interventionUte->intervenants()->create([
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

        $interventionUte->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Intervenants mis à jour avec succès',
                'data' => $interventionUte,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('intervention-utes.show', $interventionUte)
            ->with('success', 'Intervenants mis à jour avec succès.');
    }
}
