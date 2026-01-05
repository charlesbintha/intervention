@extends('layouts.app')

@section('title', 'Nouvelle Maintenance - Portail Intervention')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('maintenances.index') }}" class="text-gut-orange hover:text-opacity-75">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">Nouvelle Maintenance</h1>
        <p class="text-gray-600 mt-2">Remplissez les informations de la maintenance</p>
    </div>

    <form action="{{ route('maintenances.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-8">
        @csrf

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Informations du projet</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <div>
                <label for="project_search" class="block text-sm font-medium text-gray-700 mb-2">Rechercher un projet (Base projets) *</label>
                <div class="relative mb-2">
                    <input type="text" id="project_search" placeholder="Tapez pour rechercher un projet..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent"
                        oninput="filterProjects()">
                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                </div>
                <label for="project_name" class="block text-sm font-medium text-gray-700 mb-2">Projet sélectionné *</label>
                <select name="project_name" id="project_name" required onchange="fetchCompanyFromProject()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('project_name') border-red-500 @enderror"
                    size="5">
                    <option value="">Sélectionnez un projet</option>
                    @foreach($projects as $project)
                        <option value="{{ $project['nom_projet'] }}" data-code="{{ $project['code_projet'] ?? '' }}" data-opportunity-id="{{ $project['opportunity_id'] ?? '' }}" data-search-text="{{ strtolower(($project['code_projet'] ?? '') . ' ' . $project['nom_projet']) }}" {{ old('project_name') === $project['nom_projet'] ? 'selected' : '' }}>
                            {{ $project['code_projet'] }} - {{ $project['nom_projet'] }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    <span id="projects-count">{{ count($projects) }}</span> projet(s) affiché(s)
                </p>
                @error('project_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Nom de l'entreprise *</label>
                <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('company_name') border-red-500 @enderror">
                @error('company_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="subsidiary" class="block text-sm font-medium text-gray-700 mb-2">Filiale *</label>
                <select name="subsidiary" id="subsidiary" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('subsidiary') border-red-500 @enderror">
                    <option value="">Sélectionnez une filiale</option>
                    <option value="GUT" {{ old('subsidiary') === 'GUT' ? 'selected' : '' }}>Groupe Univers Telecom (GUT)</option>
                    <option value="CP" {{ old('subsidiary') === 'CP' ? 'selected' : '' }}>Cabinet Pencco (CP)</option>
                    <option value="UTA" {{ old('subsidiary') === 'UTA' ? 'selected' : '' }}>Univers Telecom Afrique (UTA)</option>
                    <option value="UA" {{ old('subsidiary') === 'UA' ? 'selected' : '' }}>Univers Academy (UA)</option>
                    <option value="UTE" {{ old('subsidiary') === 'UTE' ? 'selected' : '' }}>Univers Technology & Energy (UTE)</option>
                    <option value="UC" {{ old('subsidiary') === 'UC' ? 'selected' : '' }}>Univers Capital (UC)</option>
                </select>
                @error('subsidiary')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            

            <div class="md:col-span-2">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Lieu *</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('location') border-red-500 @enderror">
                @error('location')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
         <div class="border-l-4 border-gut-orange pl-4 mb-4">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Pièces jointes</h2>
            </div>
            <div class="mb-6">
                <input type="file" name="files[]" multiple class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-500">Vous pouvez ajouter plusieurs fichiers (max 10 Mo chacun).</p>
            </div>
        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Contact</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-2">Nom du contact *</label>
                <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('contact_name') border-red-500 @enderror">
                @error('contact_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_function" class="block text-sm font-medium text-gray-700 mb-2">Fonction *</label>
                <input type="text" name="contact_function" id="contact_function" value="{{ old('contact_function') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('contact_function') border-red-500 @enderror">
                @error('contact_function')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
                <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('contact_phone') border-red-500 @enderror">
                @error('contact_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Courriel *</label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('contact_email') border-red-500 @enderror">
                @error('contact_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Détails de la Maintenance</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="start_datetime" class="block text-sm font-medium text-gray-700 mb-2">Date et heure de début *</label>
                <input type="datetime-local" name="start_datetime" id="start_datetime" value="{{ old('start_datetime') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('start_datetime') border-red-500 @enderror">
                @error('start_datetime')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_datetime" class="block text-sm font-medium text-gray-700 mb-2">Date et heure de fin *</label>
                <input type="datetime-local" name="end_datetime" id="end_datetime" value="{{ old('end_datetime') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('end_datetime') border-red-500 @enderror">
                @error('end_datetime')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">But de la Maintenance *</label>
                <textarea name="purpose" id="purpose" rows="4" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                @error('purpose')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nature de l'intervention</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Mécanique" {{ is_array(old('nature_intervention')) && in_array('Mécanique', old('nature_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Mécanique</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Electrique" {{ is_array(old('nature_intervention')) && in_array('Electrique', old('nature_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Electrique</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Soudure" {{ is_array(old('nature_intervention')) && in_array('Soudure', old('nature_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Soudure</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Informatique" {{ is_array(old('nature_intervention')) && in_array('Informatique', old('nature_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Informatique</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Autre" {{ is_array(old('nature_intervention')) && in_array('Autre', old('nature_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Autre</span>
                    </label>
                </div>
                @error('nature_intervention')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Type d'intervention</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Echange de composant" {{ is_array(old('type_intervention')) && in_array('Echange de composant', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Echange de composant</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Réglage nettoyage graissage" {{ is_array(old('type_intervention')) && in_array('Réglage nettoyage graissage', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Réglage nettoyage graissage</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Réparation" {{ is_array(old('type_intervention')) && in_array('Réparation', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Réparation</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Dépannage" {{ is_array(old('type_intervention')) && in_array('Dépannage', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Dépannage</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Équipements" {{ is_array(old('type_intervention')) && in_array('Équipements', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Équipements</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Nettoyage et dépoussiérage préventif" {{ is_array(old('type_intervention')) && in_array('Nettoyage et dépoussiérage préventif', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Nettoyage et dépoussiérage préventif</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Reconfiguration, Modification, Amélioration" {{ is_array(old('type_intervention')) && in_array('Reconfiguration, Modification, Amélioration', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Reconfiguration, Modification, Amélioration</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Autre" {{ is_array(old('type_intervention')) && in_array('Autre', old('type_intervention')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Autre</span>
                    </label>
                </div>
                @error('type_intervention')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-l-4 border-gut-blue pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Intervenants</h2>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="text-sm font-medium text-gray-700">Intervenants GUT</label>
                <button type="button" onclick="addIntervenantGut()" class="text-gut-orange hover:text-opacity-75 text-sm">
                    <i class="fas fa-plus mr-1"></i>Ajouter un intervenant GUT
                </button>
            </div>
            <div id="intervenants-gut-container" class="space-y-4"></div>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="text-sm font-medium text-gray-700">Intervenants Externes</label>
                <button type="button" onclick="addIntervenantRencontre()" class="text-gut-orange hover:text-opacity-75 text-sm">
                    <i class="fas fa-plus mr-1"></i>Ajouter un intervenant externe
                </button>
            </div>
            <div id="intervenants-rencontres-container" class="space-y-4"></div>
        </div>

        <div class="flex justify-end space-x-4 pt-6 border-t">
            <a href="{{ route('maintenances.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 bg-gut-orange hover:bg-opacity-90 text-white rounded-lg transition">
                Continuer vers la mise en page <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let gutCounter = 0;
let rencontreCounter = 0;
const employees = @json($employees);

function filterProjects() {
    const searchInput = document.getElementById('project_search');
    const projectSelect = document.getElementById('project_name');
    const searchText = searchInput.value.toLowerCase().trim();

    let visibleCount = 0;

    // Parcourir toutes les options (sauf la première qui est le placeholder)
    for (let i = 1; i < projectSelect.options.length; i++) {
        const option = projectSelect.options[i];
        const optionSearchText = option.dataset.searchText || '';

        if (searchText === '' || optionSearchText.includes(searchText)) {
            option.style.display = '';
            visibleCount++;
        } else {
            option.style.display = 'none';
        }
    }

    // Mettre à jour le compteur
    document.getElementById('projects-count').textContent = visibleCount;

    // Si un seul projet correspond, le sélectionner automatiquement
    if (visibleCount === 1 && searchText !== '') {
        for (let i = 1; i < projectSelect.options.length; i++) {
            if (projectSelect.options[i].style.display !== 'none') {
                projectSelect.selectedIndex = i;
                fetchCompanyFromProject();
                break;
            }
        }
    }
}

async function fetchCompanyFromProject() {
    console.log('=== DEBUT fetchCompanyFromProject ===');
    const projectSelect = document.getElementById('project_name');
    const companyInput = document.getElementById('company_name');
    const selectedOption = projectSelect.options[projectSelect.selectedIndex];

    console.log('Projet sélectionné:', {
        value: selectedOption.value,
        text: selectedOption.text,
        dataset: selectedOption.dataset
    });

    if (!selectedOption.value) {
        console.log('Aucun projet sélectionné, reset du champ entreprise');
        companyInput.value = '';
        return;
    }

    const opportunityId = selectedOption.dataset.opportunityId;
    console.log('Opportunity ID extrait:', opportunityId);
    console.log('Opportunity ID type:', typeof opportunityId);
    console.log('Opportunity ID trim:', opportunityId ? opportunityId.trim() : 'null/undefined');

    if (opportunityId && opportunityId.trim() !== '') {
        console.log('Envoi de la requête à /get-opportunity/' + opportunityId);
        try {
            const response = await fetch(`/get-opportunity/${opportunityId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Response reçue:', {
                status: response.status,
                ok: response.ok,
                statusText: response.statusText
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Données reçues:', data);
                if (data.account_name) {
                    console.log('Nom entreprise trouvé:', data.account_name);
                    companyInput.value = data.account_name;
                } else {
                    console.warn('Pas de account_name dans la réponse');
                }
            } else {
                console.error('Response non-ok:', await response.text());
            }
        } catch (error) {
            console.error('Erreur lors de la récupération du nom de l\'entreprise:', error);
        }
    } else {
        console.log('Pas d\'opportunity_id disponible pour ce projet');
    }
    console.log('=== FIN fetchCompanyFromProject ===');
}

function addIntervenantGut() {
    const container = document.getElementById('intervenants-gut-container');
    const div = document.createElement('div');
    div.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';

    let employeesOptions = '<option value="">Sélectionnez un employé GUT</option>';
    employees.forEach(emp => {
        employeesOptions += `<option value="${emp.id}" data-nom="${emp.nom || ''}" data-prenom="${emp.prenom || ''}" data-prenom-nom="${emp.prenom_nom || ''}" data-email="${emp.email || ''}" data-telephone="${emp.telephone || ''}">${emp.prenom_nom}</option>`;
    });

    div.innerHTML = `
        <div class="mb-4">
            <select class="employee-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent" onchange="fillEmployeeDataMaintenance(this, ${gutCounter})">
                ${employeesOptions}
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="intervenants_gut[${gutCounter}][source]" value="api">
            <input type="hidden" name="intervenants_gut[${gutCounter}][api_id]" id="maint_gut_api_id_${gutCounter}">
            <div>
                <input type="text" name="intervenants_gut[${gutCounter}][nom]" id="maint_gut_nom_${gutCounter}" placeholder="Nom *" required readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="text" name="intervenants_gut[${gutCounter}][prenom]" id="maint_gut_prenom_${gutCounter}" placeholder="Prénom *" required readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="email" name="intervenants_gut[${gutCounter}][email]" id="maint_gut_email_${gutCounter}" placeholder="Email" readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="tel" name="intervenants_gut[${gutCounter}][telephone]" id="maint_gut_telephone_${gutCounter}" placeholder="Téléphone" readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="mt-2 text-red-600 hover:text-red-800 text-sm">
            <i class="fas fa-trash mr-1"></i>Supprimer
        </button>
    `;
    container.appendChild(div);
    gutCounter++;
}

function fillEmployeeDataMaintenance(select, index) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        const prenomNom = selectedOption.dataset.prenomNom || '';
        const parts = prenomNom.split(' ');
        const prenom = parts.length > 0 ? parts[0] : '';
        const nom = parts.length > 1 ? parts.slice(1).join(' ') : '';

        document.getElementById(`maint_gut_api_id_${index}`).value = selectedOption.value;
        document.getElementById(`maint_gut_nom_${index}`).value = selectedOption.dataset.nom || nom;
        document.getElementById(`maint_gut_prenom_${index}`).value = selectedOption.dataset.prenom || prenom;
        document.getElementById(`maint_gut_email_${index}`).value = selectedOption.dataset.email || '';
        document.getElementById(`maint_gut_telephone_${index}`).value = selectedOption.dataset.telephone || '';
    }
}

function addIntervenantRencontre() {
    const container = document.getElementById('intervenants-rencontres-container');
    const div = document.createElement('div');
    div.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <input type="text" name="intervenants_rencontres[${rencontreCounter}][nom]" placeholder="Nom *" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="text" name="intervenants_rencontres[${rencontreCounter}][prenom]" placeholder="Prénom *" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="email" name="intervenants_rencontres[${rencontreCounter}][email]" placeholder="Email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="tel" name="intervenants_rencontres[${rencontreCounter}][telephone]" placeholder="Téléphone"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="mt-2 text-red-600 hover:text-red-800 text-sm">
            <i class="fas fa-trash mr-1"></i>Supprimer
        </button>
    `;
    container.appendChild(div);
    rencontreCounter++;
}
</script>
@endpush
@endsection
