@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-users-cog text-gut-blue mr-2"></i>
                    Gestion des Utilisateurs
                </h1>
                <p class="mt-2 text-sm text-gray-600">Gérez les utilisateurs et leurs accès à la plateforme</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="bg-gut-blue hover:bg-gut-orange text-white px-6 py-3 rounded-lg font-medium transition shadow-lg">
                <i class="fas fa-user-plus mr-2"></i>Nouvel Utilisateur
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="gradient-gut">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-user mr-2"></i>Nom
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-shield-alt mr-2"></i>Rôle
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-toggle-on mr-2"></i>Statut
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-calendar mr-2"></i>Date de création
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-white uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full gradient-gut flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->role === 'admin')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        <i class="fas fa-crown mr-1"></i>Administrateur
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-user mr-1"></i>Utilisateur
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Actif
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-gut-blue hover:text-gut-orange transition" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline" onsubmit="handleFormSubmitWithConfirmation(event, 'toggle', '{{ $user->is_active ? 'désactiver' : 'activer' }} cet utilisateur')">
                                            @csrf
                                            <button type="submit" class="text-{{ $user->is_active ? 'orange' : 'green' }}-600 hover:text-{{ $user->is_active ? 'orange' : 'green' }}-800 transition" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.users.regenerate-password', $user) }}" method="POST" class="inline" onsubmit="handleFormSubmitWithConfirmation(event, 'password')">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 transition" title="Régénérer le mot de passe">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="handleFormSubmitWithConfirmation(event, 'delete', 'cet utilisateur')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400" title="Vous ne pouvez pas modifier votre propre compte">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-4 text-gray-400"></i>
                                <p class="text-lg">Aucun utilisateur trouvé</p>
                                <p class="text-sm mt-2">Commencez par créer un nouvel utilisateur</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <div class="mt-6 text-sm text-gray-600">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Note :</strong> Les utilisateurs inactifs ne peuvent pas se connecter à la plateforme. Vous ne pouvez pas modifier ou supprimer votre propre compte.
    </div>
</div>
@endsection
