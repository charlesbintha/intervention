@extends('layouts.app')

@section('title', 'Accueil - Portail Intervention')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Bienvenue sur le Portail d'Intervention</h1>
        <p class="text-xl text-gray-600">Sélectionnez le type d'intervention que vous souhaitez créer</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Carte Survey -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="bg-gut-blue h-2"></div>
            <div class="p-8">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-blue-100">
                    <i class="fas fa-search text-4xl text-gut-blue"></i>
                </div>
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Survey</h2>
                <p class="text-gray-600 text-center mb-6">
                    Créez un rapport de survey pour documenter vos visites d'opportunités Salesforce
                </p>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-blue mr-2"></i>
                        Opportunités Salesforce
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-blue mr-2"></i>
                        Intervenants GUT & Rencontrés
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-blue mr-2"></i>
                        Mise en page Rich Text
                    </div>
                </div>
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('surveys.create') }}" class="block text-center bg-gut-blue hover:bg-opacity-90 text-white font-semibold py-3 px-6 rounded-lg transition transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Nouveau Survey
                    </a>
                    <a href="{{ route('surveys.index') }}" class="block text-center border-2 border-gut-blue text-gut-blue hover:bg-gut-blue hover:text-white font-semibold py-3 px-6 rounded-lg transition">
                        <i class="fas fa-list mr-2"></i>Voir la liste
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte Maintenance -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="bg-gut-orange h-2"></div>
            <div class="p-8">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-orange-100">
                    <i class="fas fa-tools text-4xl text-gut-orange"></i>
                </div>
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Maintenance</h2>
                <p class="text-gray-600 text-center mb-6">
                    Documentez vos interventions de maintenance sur les projets existants
                </p>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-orange mr-2"></i>
                        Projets de la base
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-orange mr-2"></i>
                        Intervenants GUT & Rencontrés
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-orange mr-2"></i>
                        Mise en page Rich Text
                    </div>
                </div>
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('maintenances.create') }}" class="block text-center bg-gut-orange hover:bg-opacity-90 text-white font-semibold py-3 px-6 rounded-lg transition transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Nouvelle Maintenance
                    </a>
                    <a href="{{ route('maintenances.index') }}" class="block text-center border-2 border-gut-orange text-gut-orange hover:bg-gut-orange hover:text-white font-semibold py-3 px-6 rounded-lg transition">
                        <i class="fas fa-list mr-2"></i>Voir la liste
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte Intervention -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="gradient-gut h-2"></div>
            <div class="p-8">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-purple-100">
                    <i class="fas fa-wrench text-4xl" style="color: var(--gut-blue)"></i>
                </div>
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Intervention</h2>
                <p class="text-gray-600 text-center mb-6">
                    Créez un rapport d'intervention technique avec diagnostic et observations
                </p>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-blue mr-2"></i>
                        Diagnostic technique
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-blue mr-2"></i>
                        Type d'intervention
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-gut-blue mr-2"></i>
                        Observations détaillées
                    </div>
                </div>
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('intervention-utes.create') }}" class="block text-center gradient-gut hover:opacity-90 text-white font-semibold py-3 px-6 rounded-lg transition transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Nouvelle Intervention
                    </a>
                    <a href="{{ route('intervention-utes.index') }}" class="block text-center border-2 border-gut-blue text-gut-blue hover:bg-gut-blue hover:text-white font-semibold py-3 px-6 rounded-lg transition">
                        <i class="fas fa-list mr-2"></i>Voir la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- Statistiques -->
        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-gut-blue text-4xl font-bold mb-2">{{ $stats['surveys'] }}</div>
                <div class="text-gray-600">Surveys créés</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-gut-orange text-4xl font-bold mb-2">{{ $stats['maintenances'] }}</div>
                <div class="text-gray-600">Maintenances réalisées</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-gray-600 text-4xl font-bold mb-2">{{ $stats['interventions'] }}</div>
                <div class="text-gray-600">Interventions</div>
            </div>
        </div>
    @endif
</div>
@endsection
