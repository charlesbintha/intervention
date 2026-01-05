<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function editSignature()
    {
        $user = auth()->user();

        return view('profile.signature', compact('user'));
    }

    public function updateSignature(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Supprimer l'ancienne signature si elle existe
        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
        }

        // Enregistrer la nouvelle signature
        $path = $request->file('signature')->store('signatures', 'public');
        $user->update(['signature' => $path]);

        return redirect()->back()->with('success', 'Votre signature a été mise à jour avec succès.');
    }
}
