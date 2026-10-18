<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create(EmployeeService $employeeService): View
    {
        $employees = $employeeService->getEmployees();

        return view('admin.users.create', compact('employees'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => true,
        ];

        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('signatures', 'public');
            $userData['signature'] = $path;
        }

        User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé. Il pourra se connecter avec son compte Microsoft.');
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $emailChanged = $user->email !== $validated['email'];

        if ($request->hasFile('signature')) {
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }
            $validated['signature'] = $request->file('signature')->store('signatures', 'public');
        }

        if ($emailChanged) {
            $validated['microsoft_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        // Empêcher la désactivation de son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Le compte a été {$status} avec succès.");
    }

    public function getEmployees(EmployeeService $employeeService): JsonResponse
    {
        $employees = $employeeService->getEmployees();

        return response()->json($employees);
    }
}
