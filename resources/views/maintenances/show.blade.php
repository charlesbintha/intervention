@extends('layouts.app')

@section('title', 'Détails Maintenance - Portail Intervention')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <a href="{{ route('maintenances.index') }}" class="text-gut-orange hover:text-opacity-75">
                <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mt-4">{{ $maintenance->project_name }}</h1>
            <p class="text-gray-600 mt-2">Maintenance créée le {{ $maintenance->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex space-x-3">
            @if($maintenance->status != 'validated')
                @if(auth()->id() == $maintenance->user_id)
                    <form action="{{ route('maintenances.validate', $maintenance) }}" method="POST" class="inline"
                        onsubmit="handleFormSubmitWithConfirmation(event, 'validate', 'cette maintenance', 'Cette action est irréversible et empêchera toute modification ou suppression.')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                            <i class="fas fa-check mr-2"></i>Valider
                        </button>
                    </form>
                @endif

                <a href="{{ route('maintenances.edit', $maintenance) }}" class="px-4 py-2 bg-gut-blue hover:bg-opacity-90 text-white rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
            @else
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold flex items-center">
                    <i class="fas fa-lock mr-2"></i>Maintenance Validée
                </span>
            @endif

            <a href="{{ route('maintenances.pdf', $maintenance) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Générer PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="bg-gut-orange text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Informations du Projet</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($maintenance->subsidiary)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Filiale</p>
                        <p class="font-semibold text-gray-800">
                            @if($maintenance->subsidiary === 'GUT') Groupe Univers Telecom (GUT)
                            @elseif($maintenance->subsidiary === 'CP') Cabinet Pencco (CP)
                            @elseif($maintenance->subsidiary === 'UTA') Univers Telecom Afrique (UTA)
                            @elseif($maintenance->subsidiary === 'UA') Univers Academy (UA)
                            @elseif($maintenance->subsidiary === 'UTE') Univers Technology & Energy (UTE)
                            @elseif($maintenance->subsidiary === 'UC') Univers Capital (UC)
                            @else {{ $maintenance->subsidiary }}
                            @endif
                        </p>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-gray-600 mb-1">Nom du projet</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->project_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Entreprise</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->company_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Lieu</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->location }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Statut</p>
                    @if($maintenance->status === 'draft')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Brouillon</span>
                    @elseif($maintenance->status === 'pending')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                    @else
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Validé</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="bg-gut-orange text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Contact</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Nom</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->contact_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Fonction</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->contact_function }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Téléphone</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->contact_phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Courriel</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->contact_email }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="bg-gut-orange text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Détails de la Maintenance</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date et heure de début</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->start_datetime->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date et heure de fin</p>
                    <p class="font-semibold text-gray-800">{{ $maintenance->end_datetime->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">But de la Maintenance</p>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-800">{{ $maintenance->purpose }}</p>
                </div>
            </div>

            @if($maintenance->nature_intervention)
                <div>
                    <p class="text-sm text-gray-600 mb-2">Nature de l'intervention</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(', ', $maintenance->nature_intervention) as $nature)
                            <span class="px-3 py-1 bg-gut-orange bg-opacity-10 text-gut-white rounded-full text-sm font-medium">{{ $nature }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($maintenance->type_intervention)
                <div>
                    <p class="text-sm text-gray-600 mb-2">Type d'intervention</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(', ', $maintenance->type_intervention) as $type)
                            <span class="px-3 py-1 bg-gut-orange bg-opacity-10 text-gut-white rounded-full text-sm font-medium">{{ $type }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($maintenance->intervenants->count() > 0)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-gut-blue text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Intervenants</h2>
            </div>
            <div class="p-6">
                @if($maintenance->intervenantsGut->count() > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-800 mb-3">Intervenants GUT</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($maintenance->intervenantsGut as $intervenant)
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

                @if($maintenance->intervenantsRencontres->count() > 0)
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">Intervenants Rencontrés</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($maintenance->intervenantsRencontres as $intervenant)
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

    @if($maintenance->layout_content)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gut-blue to-gut-orange text-white px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Contenu du plan</h2>
                <a href="{{ route('maintenances.layout', $maintenance) }}" class="px-4 py-2 bg-white text-gut-orange rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
            </div>
            <div class="p-6">
                <div id="quill-viewer-maintenance-{{ $maintenance->id }}" class="prose max-w-none bg-gray-50 p-4 rounded-lg min-h-[100px]">
                    <p class="text-gray-500">Chargement du contenu...</p>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const viewer = document.getElementById('quill-viewer-maintenance-{{ $maintenance->id }}');

                if (!viewer) return;

                try {
                    let deltaContent = @json($maintenance->layout_content);

                    if (typeof deltaContent === 'string') {
                        try {
                            deltaContent = JSON.parse(deltaContent);
                        } catch (e) {
                            viewer.innerHTML = '<div class="text-gray-700 whitespace-pre-wrap">' + deltaContent + '</div>';
                            return;
                        }
                    }

                    const tempQuill = new Quill(viewer, {
                        theme: 'snow',
                        readOnly: true,
                        modules: {
                            toolbar: false
                        }
                    });

                    tempQuill.setContents(deltaContent);

                    const toolbar = viewer.previousElementSibling;
                    if (toolbar && toolbar.classList.contains('ql-toolbar')) {
                        toolbar.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement du contenu Quill:', error);
                    viewer.innerHTML = '<div class="text-red-500">Erreur lors du chargement du contenu formaté</div>';
                }
            });
        </script>
        @endpush
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        La mise en page n'a pas encore été complétée.
                        <a href="{{ route('maintenances.layout', $maintenance) }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Compléter la mise en page
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($maintenance->attachments && $maintenance->attachments->count() > 0)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-gut-orange text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Pieces jointes</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($maintenance->attachments as $attachment)
                        <div class="bg-gray-50 p-4 rounded-lg flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file text-gut-orange text-2xl"></i>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $attachment->original_name }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($attachment->size / 1024, 2) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ route('attachments.show', ['path' => basename($attachment->path)]) }}" target="_blank" class="text-gut-orange hover:text-opacity-75">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($maintenance->signature)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Signature</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-2">Signé par</p>
                <p class="font-semibold text-gray-800 mb-4">{{ $maintenance->signature->signer_name }}</p>
                <p class="text-sm text-gray-600">Le {{ $maintenance->signature->signed_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    @endif
</div>
@endsection
