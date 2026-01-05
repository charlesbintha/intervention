@extends('layouts.app')

@section('title', 'Modifier Maintenance - Portail Intervention')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('maintenances.show', $maintenance) }}" class="text-gut-orange hover:text-opacity-75">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la maintenance
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">Modifier la Maintenance</h1>
        <p class="text-gray-600 mt-2">{{ $maintenance->project_name }}</p>
    </div>

    <form action="{{ route('maintenances.update', $maintenance) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-8">
        @csrf
        @method('PUT')

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Informations du projet</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
           
            <div>
                <label for="project_name" class="block text-sm font-medium text-gray-700 mb-2">Nom du projet *</label>
                <input type="text" name="project_name" id="project_name" value="{{ old('project_name', $maintenance->project_name) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>

            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Nom de l'entreprise *</label>
                <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $maintenance->company_name) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
             <div>
                <label for="subsidiary" class="block text-sm font-medium text-gray-700 mb-2">Filiale</label>
                <select name="subsidiary" id="subsidiary"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent @error('subsidiary') border-red-500 @enderror">
                    <option value="">Sélectionnez une filiale</option>
                    <option value="GUT" {{ old('subsidiary', $maintenance->subsidiary) === 'GUT' ? 'selected' : '' }}>Groupe Univers Telecom (GUT)</option>
                    <option value="CP" {{ old('subsidiary', $maintenance->subsidiary) === 'CP' ? 'selected' : '' }}>Cabinet Pencco (CP)</option>
                    <option value="UTA" {{ old('subsidiary', $maintenance->subsidiary) === 'UTA' ? 'selected' : '' }}>Univers Telecom Afrique (UTA)</option>
                    <option value="UA" {{ old('subsidiary', $maintenance->subsidiary) === 'UA' ? 'selected' : '' }}>Univers Academy (UA)</option>
                    <option value="UTE" {{ old('subsidiary', $maintenance->subsidiary) === 'UTE' ? 'selected' : '' }}>Univers Technology & Energy (UTE)</option>
                    <option value="UC" {{ old('subsidiary', $maintenance->subsidiary) === 'UC' ? 'selected' : '' }}>Univers Capital (UC)</option>
                </select>
                @error('subsidiary')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Lieu *</label>
                <input type="text" name="location" id="location" value="{{ old('location', $maintenance->location) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
        </div>

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Contact</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-2">Nom du contact *</label>
                <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $maintenance->contact_name) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>

            <div>
                <label for="contact_function" class="block text-sm font-medium text-gray-700 mb-2">Fonction *</label>
                <input type="text" name="contact_function" id="contact_function" value="{{ old('contact_function', $maintenance->contact_function) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>

            <div>
                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
                <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $maintenance->contact_phone) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>

            <div>
                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Courriel *</label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $maintenance->contact_email) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
        </div>

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Détails de la Maintenance</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="start_datetime" class="block text-sm font-medium text-gray-700 mb-2">Date et heure de début *</label>
                <input type="datetime-local" name="start_datetime" id="start_datetime" value="{{ old('start_datetime', $maintenance->start_datetime->format('Y-m-d\TH:i')) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>

            <div>
                <label for="end_datetime" class="block text-sm font-medium text-gray-700 mb-2">Date et heure de fin *</label>
                <input type="datetime-local" name="end_datetime" id="end_datetime" value="{{ old('end_datetime', $maintenance->end_datetime->format('Y-m-d\TH:i')) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>

            <div class="md:col-span-2">
                <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">But de la Maintenance *</label>
                <textarea name="purpose" id="purpose" rows="4" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">{{ old('purpose', $maintenance->purpose) }}</textarea>
            </div>

            @php
                $natureSelected = old('nature_intervention', $maintenance->nature_intervention ? explode(', ', $maintenance->nature_intervention) : []);
                $typeSelected = old('type_intervention', $maintenance->type_intervention ? explode(', ', $maintenance->type_intervention) : []);
            @endphp

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nature de l'intervention</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Mécanique" {{ in_array('Mécanique', $natureSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Mécanique</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Electrique" {{ in_array('Electrique', $natureSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Electrique</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Soudure" {{ in_array('Soudure', $natureSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Soudure</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Informatique" {{ in_array('Informatique', $natureSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Informatique</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="nature_intervention[]" value="Autre" {{ in_array('Autre', $natureSelected) ? 'checked' : '' }}
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
                        <input type="checkbox" name="type_intervention[]" value="Echange de composant" {{ in_array('Echange de composant', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Echange de composant</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Réglage nettoyage graissage" {{ in_array('Réglage nettoyage graissage', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Réglage nettoyage graissage</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Réparation" {{ in_array('Réparation', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Réparation</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Dépannage" {{ in_array('Dépannage', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Dépannage</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Équipements" {{ in_array('Équipements', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Équipements</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Nettoyage et dépoussiérage préventif" {{ in_array('Nettoyage et dépoussiérage préventif', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Nettoyage et dépoussiérage préventif</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Reconfiguration, Modification, Amélioration" {{ in_array('Reconfiguration, Modification, Amélioration', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Reconfiguration, Modification, Amélioration</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="type_intervention[]" value="Autre" {{ in_array('Autre', $typeSelected) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-gut-orange focus:ring-gut-orange">
                        <span class="text-sm text-gray-700">Autre</span>
                    </label>
                </div>
                @error('type_intervention')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Pièces jointes</h2>
        </div>

        @if($maintenance->attachments && $maintenance->attachments->count() > 0)
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Fichiers actuels</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($maintenance->attachments as $attachment)
                        <div class="border border-gray-200 rounded-lg p-4 flex items-center justify-between bg-gray-50">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file text-gut-orange text-xl"></i>
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
        @endif

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Ajouter de nouveaux fichiers</label>
            <input type="file" name="files[]" multiple class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
            <p class="mt-1 text-xs text-gray-500">Vous pouvez ajouter plusieurs fichiers (max 10 Mo chacun).</p>
        </div>

        <div class="border-l-4 border-gut-orange pl-4 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Intervenants</h2>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="text-sm font-medium text-gray-700">Intervenants GUT</label>
                <button type="button" onclick="addIntervenantGut()" class="text-gut-orange hover:text-opacity-75 text-sm">
                    <i class="fas fa-plus mr-1"></i>Ajouter un intervenant GUT
                </button>
            </div>
            <div id="intervenants-gut-container" class="space-y-4">
                @foreach($maintenance->intervenantsGut as $index => $intervenant)
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="mb-4">
                            <select class="employee-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent" onchange="fillEmployeeData(this, {{ $index }})">
                                <option value="">Selectionnez un employe GUT</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp['id'] }}"
                                        data-nom="{{ $emp['nom'] ?? '' }}"
                                        data-prenom="{{ $emp['prenom'] ?? '' }}"
                                        data-prenom-nom="{{ $emp['prenom_nom'] ?? '' }}"
                                        data-email="{{ $emp['email'] ?? '' }}"
                                        data-telephone="{{ $emp['telephone'] ?? '' }}"
                                        {{ $intervenant->api_id == $emp['id'] ? 'selected' : '' }}>
                                        {{ $emp['prenom_nom'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="hidden" name="intervenants_gut[{{ $index }}][id]" value="{{ $intervenant->id }}">
                            <input type="hidden" name="intervenants_gut[{{ $index }}][source]" value="api">
                            <input type="hidden" name="intervenants_gut[{{ $index }}][api_id]" id="gut_api_id_{{ $index }}" value="{{ $intervenant->api_id }}">
                            <div>
                                <input type="text" name="intervenants_gut[{{ $index }}][nom]" id="gut_nom_{{ $index }}" placeholder="Nom *" value="{{ $intervenant->nom }}" required readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                            <div>
                                <input type="text" name="intervenants_gut[{{ $index }}][prenom]" id="gut_prenom_{{ $index }}" placeholder="Prenom *" value="{{ $intervenant->prenom }}" required readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                            <div>
                                <input type="email" name="intervenants_gut[{{ $index }}][email]" id="gut_email_{{ $index }}" placeholder="Email" value="{{ $intervenant->email }}" readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                            <div>
                                <input type="tel" name="intervenants_gut[{{ $index }}][telephone]" id="gut_telephone_{{ $index }}" placeholder="Telephone" value="{{ $intervenant->telephone }}" readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                        </div>
                        <button type="button" onclick="markForDeletion(this, {{ $intervenant->id }})" class="mt-2 text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="text-sm font-medium text-gray-700">Intervenants Externes</label>
                <button type="button" onclick="addIntervenantRencontre()" class="text-gut-orange hover:text-opacity-75 text-sm">
                    <i class="fas fa-plus mr-1"></i>Ajouter un intervenant externe
                </button>
            </div>
            <div id="intervenants-rencontres-container" class="space-y-4">
                @foreach($maintenance->intervenantsRencontres as $index => $intervenant)
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="hidden" name="intervenants_rencontres[{{ $index }}][id]" value="{{ $intervenant->id }}">
                            <div>
                                <input type="text" name="intervenants_rencontres[{{ $index }}][nom]" placeholder="Nom *" value="{{ $intervenant->nom }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                            <div>
                                <input type="text" name="intervenants_rencontres[{{ $index }}][prenom]" placeholder="Prenom *" value="{{ $intervenant->prenom }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                            <div>
                                <input type="email" name="intervenants_rencontres[{{ $index }}][email]" placeholder="Email" value="{{ $intervenant->email }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                            <div>
                                <input type="tel" name="intervenants_rencontres[{{ $index }}][telephone]" placeholder="Telephone" value="{{ $intervenant->telephone }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
                            </div>
                        </div>
                        <button type="button" onclick="markForDeletion(this, {{ $intervenant->id }})" class="mt-2 text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end space-x-4 pt-6 border-t">
            <a href="{{ route('maintenances.show', $maintenance) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 bg-gut-orange hover:bg-opacity-90 text-white rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let gutCounter = {{ $maintenance->intervenantsGut->count() }};
let rencontreCounter = {{ $maintenance->intervenantsRencontres->count() }};
const employees = @json($employees);

function markForDeletion(button, intervenantId) {
    const container = button.closest('.border');
    container.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete_intervenants[]';
    input.value = intervenantId;
    container.appendChild(input);
}

function addIntervenantGut() {
    const container = document.getElementById('intervenants-gut-container');
    const div = document.createElement('div');
    div.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';

    let employeesOptions = '<option value="">Selectionnez un employe GUT</option>';
    employees.forEach(emp => {
        employeesOptions += `<option value="${emp.id}" data-nom="${emp.nom || ''}" data-prenom="${emp.prenom || ''}" data-prenom-nom="${emp.prenom_nom || ''}" data-email="${emp.email || ''}" data-telephone="${emp.telephone || ''}">${emp.prenom_nom}</option>`;
    });

    div.innerHTML = `
        <div class="mb-4">
            <select class="employee-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent" onchange="fillEmployeeData(this, ${gutCounter})">
                ${employeesOptions}
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="intervenants_gut[${gutCounter}][source]" value="api">
            <input type="hidden" name="intervenants_gut[${gutCounter}][api_id]" id="gut_api_id_${gutCounter}">
            <div>
                <input type="text" name="intervenants_gut[${gutCounter}][nom]" id="gut_nom_${gutCounter}" placeholder="Nom *" required readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="text" name="intervenants_gut[${gutCounter}][prenom]" id="gut_prenom_${gutCounter}" placeholder="Prenom *" required readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="email" name="intervenants_gut[${gutCounter}][email]" id="gut_email_${gutCounter}" placeholder="Email" readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="tel" name="intervenants_gut[${gutCounter}][telephone]" id="gut_telephone_${gutCounter}" placeholder="Telephone" readonly
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

function fillEmployeeData(select, index) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        const prenomNom = selectedOption.dataset.prenomNom || '';
        const parts = prenomNom.split(' ');
        const prenom = parts.length > 0 ? parts[0] : '';
        const nom = parts.length > 1 ? parts.slice(1).join(' ') : '';

        document.getElementById(`gut_api_id_${index}`).value = selectedOption.value;
        document.getElementById(`gut_nom_${index}`).value = selectedOption.dataset.nom || nom;
        document.getElementById(`gut_prenom_${index}`).value = selectedOption.dataset.prenom || prenom;
        document.getElementById(`gut_email_${index}`).value = selectedOption.dataset.email || '';
        document.getElementById(`gut_telephone_${index}`).value = selectedOption.dataset.telephone || '';
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
                <input type="text" name="intervenants_rencontres[${rencontreCounter}][prenom]" placeholder="Prenom *" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="email" name="intervenants_rencontres[${rencontreCounter}][email]" placeholder="Email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-orange focus:border-transparent">
            </div>
            <div>
                <input type="tel" name="intervenants_rencontres[${rencontreCounter}][telephone]" placeholder="Telephone"
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
