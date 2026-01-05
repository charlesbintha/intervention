@extends('layouts.app')

@section('title', 'Détails Intervention - Portail Intervention')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <a href="{{ route('intervention-utes.index') }}" class="text-gut-blue hover:text-opacity-75">
                <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mt-4">Intervention - {{ $interventionUte->company_name }}</h1>
            <p class="text-gray-600 mt-2">Intervention créée le {{ $interventionUte->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex space-x-3">
            @if($interventionUte->status != 'validated')
                @if(auth()->id() == $interventionUte->user_id)
                    <form action="{{ route('intervention-utes.validate', $interventionUte) }}" method="POST" class="inline"
                        onsubmit="handleFormSubmitWithConfirmation(event, 'validate', 'cette intervention', 'Cette action est irréversible et empêchera toute modification ou suppression.')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                            <i class="fas fa-check mr-2"></i>Valider
                        </button>
                    </form>
                @endif

                <a href="{{ route('intervention-utes.edit', $interventionUte) }}" class="px-4 py-2 bg-gut-orange hover:bg-opacity-90 text-white rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
            @else
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold flex items-center">
                    <i class="fas fa-lock mr-2"></i>Intervention Validée
                </span>
            @endif

            <a href="{{ route('intervention-utes.pdf', $interventionUte) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Générer PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="gradient-gut text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Informations de l'Entreprise</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($interventionUte->subsidiary)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Filiale</p>
                        <p class="font-semibold text-gray-800">
                            @if($interventionUte->subsidiary === 'GUT') Groupe Univers Telecom (GUT)
                            @elseif($interventionUte->subsidiary === 'CP') Cabinet Pencco (CP)
                            @elseif($interventionUte->subsidiary === 'UTA') Univers Telecom Afrique (UTA)
                            @elseif($interventionUte->subsidiary === 'UA') Univers Academy (UA)
                            @elseif($interventionUte->subsidiary === 'UTE') Univers Technology & Energy (UTE)
                            @elseif($interventionUte->subsidiary === 'UC') Univers Capital (UC)
                            @else {{ $interventionUte->subsidiary }}
                            @endif
                        </p>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-gray-600 mb-1">Entreprise</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->company_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Lieu</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->location }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Statut</p>
                    @if($interventionUte->status === 'draft')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Brouillon</span>
                    @elseif($interventionUte->status === 'pending')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                    @else
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Validé</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="bg-gut-blue text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Contact</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Nom</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->contact_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Fonction</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->contact_function }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Téléphone</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->contact_phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Courriel</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->contact_email }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="bg-gut-orange text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Détails de l'Intervention</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date et heure de début</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->start_datetime->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date et heure de fin</p>
                    <p class="font-semibold text-gray-800">{{ $interventionUte->end_datetime->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Diagnostic</p>
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst($interventionUte->diagnostic) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Type d'intervention</p>
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-orange-100 text-orange-800">
                        {{ ucfirst(str_replace('_', ' ', $interventionUte->type)) }}
                    </span>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-2">But de l'intervention</p>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-800">{{ $interventionUte->purpose }}</p>
                </div>
            </div>

            @if($interventionUte->observations)
                <div>
                    <p class="text-sm text-gray-600 mb-2">Observations / Recommandations</p>
                    <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                        <p class="text-gray-800">{{ $interventionUte->observations }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($interventionUte->intervenants->count() > 0)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-gut-blue text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Intervenants</h2>
            </div>
            <div class="p-6">
                @if($interventionUte->intervenantsGut->count() > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-800 mb-3">Intervenants GUT</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($interventionUte->intervenantsGut as $intervenant)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="font-semibold text-gray-800">{{ $intervenant->prenom }} {{ $intervenant->nom }}</p>
                                    @if($intervenant->email)
                                        <p class="text-sm text-gray-600"><i class="fas fa-envelope mr-1"></i>{{ $intervenant->email }}</p>
                                    @endif
                                    @if($intervenant->telephone)
                                        <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>{{ $intervenant->telephone }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($interventionUte->intervenantsRencontres->count() > 0)
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">Intervenants Rencontrés</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($interventionUte->intervenantsRencontres as $intervenant)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="font-semibold text-gray-800">{{ $intervenant->prenom }} {{ $intervenant->nom }}</p>
                                    @if($intervenant->email)
                                        <p class="text-sm text-gray-600"><i class="fas fa-envelope mr-1"></i>{{ $intervenant->email }}</p>
                                    @endif
                                    @if($intervenant->telephone)
                                        <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>{{ $intervenant->telephone }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($interventionUte->attachments && $interventionUte->attachments->count() > 0)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="gradient-gut text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Pieces jointes</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($interventionUte->attachments as $attachment)
                        <div class="bg-gray-50 p-4 rounded-lg flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file text-gut-blue text-2xl"></i>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $attachment->original_name }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($attachment->size / 1024, 2) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ route('attachments.show', ['path' => basename($attachment->path)]) }}" target="_blank" class="text-gut-blue hover:text-opacity-75">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($interventionUte->signature)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Signature</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-2">Signé par</p>
                <p class="font-semibold text-gray-800 mb-4">{{ $interventionUte->signature->signer_name }}</p>
                <p class="text-sm text-gray-600">Le {{ $interventionUte->signature->signed_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    @endif
</div>
@endsection
