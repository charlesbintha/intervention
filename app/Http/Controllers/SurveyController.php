<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Services\EmployeeService;
use App\Services\SalesforceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    protected $salesforceService;

    protected $employeeService;

    public function __construct(SalesforceService $salesforceService, EmployeeService $employeeService)
    {
        $this->salesforceService = $salesforceService;
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        // Admin can see all, regular users only see their own
        $query = Survey::with('intervenants', 'signature', 'user');

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $surveys = $query->latest()->get();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($surveys);
        }

        // Web request - return view
        return view('surveys.index', compact('surveys'));
    }

    public function create()
    {
        $opportunities = $this->salesforceService->getOpportunities();
        $employees = $this->employeeService->getEmployees();

        return view('surveys.create', compact('opportunities', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subsidiary' => 'required|in:GUT,CP,UTA,UA,UTE,UC',
            'opportunity_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_function' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
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

        $survey = null;

        DB::transaction(function () use ($validated, $request, &$survey) {
            $survey = Survey::create([
                ...$validated,
                'user_id' => auth()->id(),
            ]);

            if ($request->has('intervenants_gut')) {
                foreach ($request->intervenants_gut as $intervenant) {
                    $survey->intervenants()->create([
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
                    $survey->intervenants()->create([
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
                $survey->attachments()->create([
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
                'message' => 'Survey créé avec succès',
                'data' => $survey,
            ], 201);
        }

        // Web request - return redirect
        session()->flash('survey_id', $survey->id);

        return redirect()->route('surveys.layout', $survey->id)
            ->with('success', 'Survey créé avec succès. Veuillez compléter la mise en page.');
    }

    public function show(Request $request, $survey)
    {
        $id = (int) $survey;

        $query = Survey::with('intervenants', 'signature', 'user')
            ->whereKey($id);

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $record = $query->firstOrFail();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($record);
        }

        return view('surveys.show', ['survey' => $record]);
    }

    public function edit(Survey $survey)
    {
        // Check authorization: admin can edit all, regular users only their own
        if (! auth()->user()->isAdmin() && (int) $survey->user_id !== (int) auth()->id()) {
            abort(403);
        }

        // Check if already validated - prevent editing
        if ($survey->status === 'validated') {
            abort(403, 'Ce survey validé ne peut pas être modifié.');
        }

        $survey->load('intervenants');
        $employees = $this->employeeService->getEmployees();

        return view('surveys.edit', compact('survey', 'employees'));
    }

    public function update(Request $request, Survey $survey)
    {
        // Check authorization: admin can update all, regular users only their own
        if (! auth()->user()->isAdmin() && $survey->user_id !== auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if already validated - prevent updates
        if ($survey->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Ce survey validé ne peut pas être modifié.'], 403);
            }

            abort(403, 'Ce survey validé ne peut pas être modifié.');
        }

        $validated = $request->validate([
            'subsidiary' => 'nullable|in:GUT,CP,UTA,UA,UTE,UC',
            'opportunity_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_function' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
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

        DB::transaction(function () use ($validated, $request, $survey) {
            $survey->update($validated);

            // Update intervenants if provided
            if ($request->has('intervenants_gut')) {
                // Delete existing GUT intervenants
                $survey->intervenantsGut()->delete();

                // Create new GUT intervenants
                foreach ($request->intervenants_gut as $intervenant) {
                    $survey->intervenants()->create([
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
                $survey->intervenantsRencontres()->delete();

                // Create new rencontres intervenants
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $survey->intervenants()->create([
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
                $survey->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $survey->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Survey mis à jour avec succès',
                'data' => $survey,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Survey mis à jour avec succès.');
    }

    public function destroy(Request $request, Survey $survey)
    {
        // Check authorization: admin can delete all, regular users only their own
        if (! auth()->user()->isAdmin() && $survey->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if already validated - prevent deletion
        if ($survey->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Ce survey validé ne peut pas être supprimé.'], 403);
            }

            abort(403, 'Ce survey validé ne peut pas être supprimé.');
        }

        $survey->delete();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Survey supprimé avec succès',
            ]);
        }

        // Web request - return redirect
        return redirect()->route('surveys.index')
            ->with('success', 'Survey supprimé avec succès.');
    }

    public function layout(Survey $survey)
    {
        return view('surveys.layout', compact('survey'));
    }

    public function storeLayout(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'layout_content' => 'required|string',
        ]);

        $survey->update([
            'layout_content' => $validated['layout_content'],
            'status' => 'pending',
        ]);

        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Mise en page enregistrée avec succès.');
    }

    public function generatePDF(Request $request, Survey $survey)
    {
        // Check authorization: admin can see all, regular users only their own
        if (! auth()->user()->isAdmin() && $survey->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        $survey->load('intervenants', 'signature', 'user');

        // API request - return JSON with PDF URL or generate PDF
        if ($request->expectsJson() || $request->is('api/*')) {
            // For API, we return the view HTML as JSON or redirect to PDF
            // You might want to use a PDF generation library like DomPDF or Snappy
            // For now, we'll return a message indicating PDF generation
            return response()->json([
                'message' => 'PDF generation endpoint',
                'survey_id' => $survey->id,
                'note' => 'Use the web interface to generate PDF or implement PDF generation library',
            ]);
        }

        // Web request - return view
        return view('surveys.pdf', compact('survey'));
    }

    public function storeSignature(Request $request, Survey $survey)
    {
        // Check authorization: admin can sign all, regular users only their own
        if (! auth()->user()->isAdmin() && $survey->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        $validated = $request->validate([
            'signature_data' => 'required|string',
            'signer_name' => 'required|string|max:255',
        ]);

        $signature = $survey->signature()->updateOrCreate(
            ['signable_id' => $survey->id, 'signable_type' => Survey::class],
            [
                'signature_data' => $validated['signature_data'],
                'signer_name' => $validated['signer_name'],
                'signed_at' => now(),
            ]
        );

        // Update status to validated after signature
        $survey->update(['status' => 'validated']);

        $survey->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Signature enregistrée avec succès',
                'data' => $survey,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Signature enregistrée avec succès.');
    }

    public function validate(Request $request, Survey $survey): mixed
    {
        // Authorization: Owner only (not admin) can validate their own surveys
        if ($survey->user_id != auth()->id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403, 'Vous n\'êtes pas autorisé à valider ce survey.');
        }

        // Check if already validated - prevent double validation
        if ($survey->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Ce survey est déjà validé.'], 409);
            }

            return redirect()->route('surveys.show', $survey)
                ->with('info', 'Ce survey est déjà validé.');
        }

        // Update status to validated
        $survey->update(['status' => 'validated']);

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Survey validé avec succès',
                'data' => $survey,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Survey validé avec succès.');
    }

    public function updateIntervenants(Request $request, Survey $survey): mixed
    {
        // Authorization: Only owner can update intervenants
        if ($survey->user_id != auth()->id() && ! auth()->user()->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403);
        }

        // Check if survey is already validated
        if ($survey->status === 'validated') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Ce survey validé ne peut pas être modifié.'], 403);
            }

            abort(403, 'Ce survey validé ne peut pas être modifié.');
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

        DB::transaction(function () use ($request, $survey) {
            // Update GUT intervenants if provided
            if ($request->has('intervenants_gut')) {
                // Delete existing GUT intervenants
                $survey->intervenantsGut()->delete();

                // Create new GUT intervenants
                foreach ($request->intervenants_gut as $intervenant) {
                    $survey->intervenants()->create([
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
                $survey->intervenantsRencontres()->delete();

                // Create new rencontres intervenants
                foreach ($request->intervenants_rencontres as $intervenant) {
                    $survey->intervenants()->create([
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

        $survey->load('intervenants', 'signature', 'user');

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Intervenants mis à jour avec succès',
                'data' => $survey,
            ]);
        }

        // Web request - return redirect
        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Intervenants mis à jour avec succès.');
    }
}
