@extends('layouts.app')

@section('title', 'Modifier un Utilisateur')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gut-blue hover:text-gut-orange transition inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-user-edit text-gut-blue mr-2"></i>
            Modifier l'Utilisateur
        </h1>
        <p class="mt-2 text-sm text-gray-600">Modifiez les informations de l'utilisateur</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Informations actuelles -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-info-circle mr-2 text-gut-blue"></i>Informations actuelles
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Créé le :</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Statut :</span>
                            @if($user->is_active)
                                <span class="ml-2 px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Actif
                                </span>
                            @else
                                <span class="ml-2 px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Inactif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Nom complet -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-gut-blue"></i>Nom complet *
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Ex: Amadou Diallo">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-gut-blue"></i>Adresse email *
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="exemple@gut.sn">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Rôle -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-shield-alt mr-2 text-gut-blue"></i>Rôle *
                    </label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">Sélectionnez un rôle</option>
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                            Utilisateur
                        </option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                            Administrateur
                        </option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <div class="mt-2 space-y-2 text-xs text-gray-600">
                        <p>
                            <i class="fas fa-user text-blue-500 mr-1"></i>
                            <strong>Utilisateur :</strong> Peut seulement soumettre les formulaires
                        </p>
                        <p>
                            <i class="fas fa-crown text-purple-500 mr-1"></i>
                            <strong>Administrateur :</strong> Accès complet incluant la gestion des utilisateurs
                        </p>
                    </div>
                </div>

                <!-- Signature -->
                <div>
                    <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-signature mr-2 text-gut-blue"></i>Signature
                    </label>
                    @if($user->signature)
                        <div class="mb-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <i class="fas fa-check-circle text-green-600 mr-1"></i>Signature actuelle :
                            </p>
                            <img src="{{ asset('storage/' . $user->signature) }}" alt="Signature" class="max-h-24 border border-gray-300 rounded">
                        </div>
                    @endif
                    <input type="file" name="signature" id="signature" accept="image/png,image/jpeg,image/jpg"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gut-blue file:text-white hover:file:bg-gut-orange @error('signature') border-red-500 @enderror">
                    @error('signature')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>Formats acceptés: PNG, JPG, JPEG. Taille maximale: 2 Mo{{ $user->signature ? '. Laissez vide pour conserver la signature actuelle.' : '' }}
                    </p>
                </div>

                <!-- Actions rapides -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800">Actions rapides disponibles</h3>
                            <div class="mt-2 text-sm text-gray-700 space-y-2">
                                <p>
                                    <i class="fas fa-key text-blue-600 mr-2"></i>
                                    Pour réinitialiser le mot de passe, retournez à la liste et utilisez l'icône
                                    <i class="fas fa-key text-xs"></i>
                                </p>
                                <p>
                                    <i class="fas fa-ban text-orange-600 mr-2"></i>
                                    Pour désactiver le compte, utilisez l'icône
                                    <i class="fas fa-ban text-xs"></i> dans la liste
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="bg-gut-blue hover:bg-gut-orange text-white px-8 py-3 rounded-lg font-medium transition shadow-lg">
                    <i class="fas fa-save mr-2"></i>Enregistrer les Modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
