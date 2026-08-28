@extends('layouts.app')

@section('title', 'Nouveau suivi de travaux')

@section('content')
<div class="tracking-ui max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('project-trackings.index') }}" class="text-sm font-semibold text-gut-blue">← Retour aux suivis</a>
        <h1 class="mt-3 text-3xl font-bold text-gray-900">Nouveau suivi de travaux</h1>
        <p class="mt-1 text-gray-600">Sélectionnez le projet externe et sa filiale avant de construire le planning terrain.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('project-trackings.store') }}" method="POST" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        @csrf
        <div>
            <label for="project_search" class="block text-sm font-semibold text-gray-700">Rechercher un projet <span class="text-red-500">*</span></label>
            <div class="relative mt-2">
                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="search" id="project_search" placeholder="Saisir un code ou un nom de projet..." autocomplete="off" class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
            </div>
            <label for="project_selector" class="mt-4 block text-sm font-semibold text-gray-700">Projet sélectionné</label>
            <select id="project_selector" name="external_project_code" required class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                <option value="">Sélectionner un projet</option>
                @foreach($projects as $project)
                    <option value="{{ $project['code_projet'] }}" data-name="{{ $project['nom_projet'] }}" data-opportunity="{{ $project['opportunity_id'] }}" data-client="{{ $project['client_name'] ?? '' }}" data-search-text="{{ strtolower($project['display']) }}" @selected(old('external_project_code') === $project['code_projet'])>
                        {{ $project['display'] }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500"><span id="projects_count">{{ $projects->count() }}</span> projet(s) disponible(s)</p>
            @if($projects->isEmpty())<p class="mt-2 text-sm text-amber-700">Aucun projet n’a pu être chargé depuis la plateforme externe.</p>@endif
            <input type="hidden" id="external_project_name" name="external_project_name" value="{{ old('external_project_name') }}">
            <input type="hidden" id="external_opportunity_id" name="external_opportunity_id" value="{{ old('external_opportunity_id') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700">Filiale <span class="text-red-500">*</span></label>
            <select name="subsidiary" required class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                <option value="">Sélectionner une filiale</option>
                @foreach(['GUT' => 'Groupe Univers Telecom', 'CP' => 'Cabinet Pencco', 'UTA' => 'Univers Telecom Afrique', 'UA' => 'Univers Academy', 'UTE' => 'Univers Technology & Energy', 'UC' => 'Univers Capital'] as $code => $name)
                    <option value="{{ $code }}" @selected(old('subsidiary') === $code)>{{ $code }} — {{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Client du projet</label>
                <input name="client_name" id="client_name" value="{{ old('client_name') }}" readonly placeholder="Sélectionnez d’abord un projet" class="mt-2 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 py-2.5 text-gray-700">
                <p id="client_status" class="mt-1 text-xs text-gray-500">Cette information est renseignée automatiquement.</p>
            </div>
            <div><label class="block text-sm font-semibold text-gray-700">Localisation / chantier</label><input name="location" value="{{ old('location') }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"></div>
            <div><label class="block text-sm font-semibold text-gray-700">Début prévisionnel</label><input type="date" name="current_start_date" value="{{ old('current_start_date') }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"></div>
            <div><label class="block text-sm font-semibold text-gray-700">Fin prévisionnelle</label><input type="date" name="current_end_date" value="{{ old('current_end_date') }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"></div>
        </div>
        <div><label class="block text-sm font-semibold text-gray-700">Description générale</label><textarea name="description" rows="3" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">{{ old('description') }}</textarea></div>
        <div class="flex justify-end"><button id="submit_button" class="cursor-pointer rounded-lg bg-gut-blue px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2">Créer et définir le planning</button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const projectSearch = document.getElementById('project_search');
    const projectSelector = document.getElementById('project_selector');
    const clientInput = document.getElementById('client_name');
    const clientStatus = document.getElementById('client_status');
    const projectOptions = Array.from(projectSelector.options).slice(1);

    projectSearch.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        let visibleCount = 0;

        projectOptions.forEach(option => {
            const isVisible = !term || option.dataset.searchText.includes(term);
            option.hidden = !isVisible;
            if (isVisible) visibleCount++;
        });

        document.getElementById('projects_count').textContent = visibleCount;
    });

    const syncProject = async () => {
        const option = projectSelector.options[projectSelector.selectedIndex];
        document.getElementById('external_project_name').value = option?.dataset.name || '';
        document.getElementById('external_opportunity_id').value = option?.dataset.opportunity || '';
        projectSearch.value = option?.dataset.name ? option.textContent.trim() : projectSearch.value;

        if (!option?.value) {
            clientInput.value = '';
            clientStatus.textContent = 'Cette information est renseignée automatiquement.';
            return;
        }

        clientInput.value = option.dataset.client || '';
        if (clientInput.value) {
            clientStatus.textContent = 'Client récupéré depuis le projet.';
            return;
        }

        if (!option.dataset.opportunity) {
            clientStatus.textContent = 'Aucun client n’est renseigné sur ce projet.';
            return;
        }

        clientInput.placeholder = 'Chargement du client...';
        clientStatus.textContent = 'Récupération du client associé au projet...';

        try {
            const response = await fetch(`/get-opportunity/${encodeURIComponent(option.dataset.opportunity)}`);
            if (!response.ok) throw new Error('Client indisponible');
            const opportunity = await response.json();
            clientInput.value = opportunity.account_name || '';
            clientStatus.textContent = clientInput.value ? 'Client récupéré depuis le projet.' : 'Aucun client n’est renseigné sur ce projet.';
        } catch (error) {
            clientInput.value = '';
            clientStatus.textContent = 'Le client n’a pas pu être récupéré pour le moment.';
        } finally {
            clientInput.placeholder = 'Client non renseigné dans le projet';
        }
    };
    projectSelector.addEventListener('change', syncProject);
    if (projectSelector.value && !document.getElementById('external_project_name').value) syncProject();
</script>
@endpush
