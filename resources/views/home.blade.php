@extends('layouts.app')

@section('title', 'Accueil - Portail Intervention')

@section('content')
<div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Bienvenue sur le Portail d'Intervention</h1>
        <p class="text-base text-gray-600">Sélectionnez le type d'intervention que vous souhaitez créer</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Carte Survey -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="bg-gut-blue h-2"></div>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100">
                    <i class="fas fa-search text-3xl text-gut-blue"></i>
                </div>
                <h2 class="text-xl font-bold text-center text-gray-800 mb-2">Survey</h2>
                <p class="text-sm leading-5 text-gray-600 text-center mb-5 min-h-10">
                    Créez un rapport de survey pour documenter vos visites d'opportunités Salesforce
                </p>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('surveys.create') }}" class="block text-center bg-gut-blue hover:opacity-90 text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nouveau Survey
                    </a>
                    <a href="{{ route('surveys.index') }}" class="block text-center border border-gut-blue text-gut-blue hover:bg-gut-blue hover:text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fas fa-list mr-2"></i>Voir la liste
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte Suivi des travaux -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="bg-emerald-600 h-2"></div>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100">
                    <i class="fas fa-list-check text-3xl text-emerald-600"></i>
                </div>
                <h2 class="text-xl font-bold text-center text-gray-800 mb-2">Suivi travaux</h2>
                <p class="text-sm leading-5 text-gray-600 text-center mb-5 min-h-10">Planifiez les activités terrain et suivez leur exécution réelle.</p>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('project-trackings.create') }}" class="block text-center bg-emerald-600 text-white text-sm font-semibold py-2.5 px-4 rounded-lg hover:bg-emerald-700 transition-colors">Nouveau suivi</a>
                    <a href="{{ route('project-trackings.index') }}" class="block text-center border border-emerald-600 text-emerald-700 text-sm font-semibold py-2.5 px-4 rounded-lg hover:bg-emerald-50 transition-colors">Voir les projets</a>
                </div>
            </div>
        </div>

        <!-- Carte Maintenance -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="bg-gut-orange h-2"></div>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-orange-100">
                    <i class="fas fa-tools text-3xl text-gut-orange"></i>
                </div>
                <h2 class="text-xl font-bold text-center text-gray-800 mb-2">Maintenance</h2>
                <p class="text-sm leading-5 text-gray-600 text-center mb-5 min-h-10">
                    Documentez vos interventions de maintenance sur les projets existants
                </p>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('maintenances.create') }}" class="block text-center bg-gut-orange hover:opacity-90 text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nouvelle Maintenance
                    </a>
                    <a href="{{ route('maintenances.index') }}" class="block text-center border border-gut-orange text-gut-orange hover:bg-gut-orange hover:text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fas fa-list mr-2"></i>Voir la liste
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte Intervention -->
        <div class="card-hover card-shadow bg-white rounded-xl overflow-hidden">
            <div class="gradient-gut h-2"></div>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100">
                    <i class="fas fa-wrench text-3xl" style="color: var(--gut-blue)"></i>
                </div>
                <h2 class="text-xl font-bold text-center text-gray-800 mb-2">Intervention</h2>
                <p class="text-sm leading-5 text-gray-600 text-center mb-5 min-h-10">
                    Créez un rapport d'intervention technique avec diagnostic et observations
                </p>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('intervention-utes.create') }}" class="block text-center gradient-gut hover:opacity-90 text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nouvelle Intervention
                    </a>
                    <a href="{{ route('intervention-utes.index') }}" class="block text-center border border-gut-blue text-gut-blue hover:bg-gut-blue hover:text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fas fa-list mr-2"></i>Voir la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- Statistiques -->
        <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-gut-blue text-4xl font-bold mb-2">{{ $stats['surveys'] }}</div>
                <div class="text-gray-600">Surveys créés</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-emerald-600 text-4xl font-bold mb-2">{{ $stats['project_trackings'] }}</div>
                <div class="text-gray-600">Suivis de travaux</div>
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
