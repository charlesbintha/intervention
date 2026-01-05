<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewUserCredentials;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create(EmployeeService $employeeService)
    {
        $employees = $employeeService->getEmployees();

        return view('admin.users.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,user',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Générer un mot de passe aléatoire
        $password = Str::random(12);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($password),
            'is_active' => true,
        ];

        // Gérer l'upload de la signature
        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('signatures', 'public');
            $userData['signature'] = $path;
        }

        $user = User::create($userData);

        // Envoyer l'email avec les identifiants
        $user->notify(new NewUserCredentials($password));

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur créé avec succès. Un email a été envoyé à {$user->email} avec ses identifiants.");
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,user',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Gérer l'upload de la signature
        if ($request->hasFile('signature')) {
            // Supprimer l'ancienne signature si elle existe
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }
            $validated['signature'] = $request->file('signature')->store('signatures', 'public');
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user)
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

    public function regeneratePassword(User $user)
    {
        // Générer un nouveau mot de passe
        $password = Str::random(12);

        $user->update([
            'password' => Hash::make($password),
        ]);

        // Envoyer l'email avec le nouveau mot de passe
        $user->notify(new NewUserCredentials($password, true));

        return back()->with('success', "Un nouveau mot de passe a été généré et envoyé à {$user->email}.");
    }

    public function getEmployees(EmployeeService $employeeService)
    {
        $employees = $employeeService->getEmployees();

        return response()->json($employees);
    }
}
