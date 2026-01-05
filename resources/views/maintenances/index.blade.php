@extends('layouts.app')

@section('title', 'Maintenances - Portail Intervention')

@section('content')
<div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Liste des Maintenances</h1>
            <p class="text-gray-600 mt-2">Gérez toutes vos maintenances</p>
        </div>
        <a href="{{ route('maintenances.create') }}" class="bg-gut-orange hover:bg-opacity-90 text-white font-semibold py-3 px-6 rounded-lg transition transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i>Nouvelle Maintenance
        </a>
    </div>

    @if($maintenances->isEmpty())
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <i class="fas fa-tools text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucune maintenance créée</h3>
            <p class="text-gray-500 mb-6">Commencez par créer votre première maintenance</p>
            <a href="{{ route('maintenances.create') }}" class="inline-block bg-gut-orange hover:bg-opacity-90 text-white font-semibold py-3 px-6 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Créer une maintenance
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entreprise</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lieu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rédigé par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($maintenances as $maintenance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap max-w-xs">
                                <div class="text-sm font-medium text-gray-900 truncate" title="{{ $maintenance->project_name }}">{{ $maintenance->project_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $maintenance->company_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $maintenance->location }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $maintenance->start_datetime->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $maintenance->user->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($maintenance->status === 'draft')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Brouillon</span>
                                @elseif($maintenance->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Validé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('maintenances.show', $maintenance) }}" class="text-gut-orange hover:text-opacity-75 mr-3" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($maintenance->status !== 'validated')
                                    <a href="{{ route('maintenances.edit', $maintenance) }}" class="text-gut-blue hover:text-opacity-75 mr-3" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <a href="{{ route('maintenances.pdf', $maintenance) }}" class="text-green-600 hover:text-green-900 mr-3" title="PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @if($maintenance->status !== 'validated')
                                    <form action="{{ route('maintenances.destroy', $maintenance) }}" method="POST" class="inline" onsubmit="handleFormSubmitWithConfirmation(event, 'delete', 'cette maintenance')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
