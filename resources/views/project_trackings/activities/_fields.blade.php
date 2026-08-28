@php
    $selectedAgents = array_values(old('assigned_agents', isset($activity) ? ($activity->assigned_agents ?? []) : []));
    $agentChoices = collect($employees)->pluck('prenom_nom')->filter()->merge($selectedAgents)->unique()->sort()->values();
    $externalStakeholders = array_values(old('external_stakeholders', isset($activity) ? ($activity->external_stakeholders ?? []) : []));
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div><label class="block text-sm font-semibold">Lot <span class="text-red-500">*</span></label><input name="lot_name" required value="{{ old('lot_name', $activity->lot_name ?? '') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    <div><label class="block text-sm font-semibold">Phase</label><input name="phase_name" value="{{ old('phase_name', $activity->phase_name ?? '') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    <div class="md:col-span-2"><label class="block text-sm font-semibold">Activité <span class="text-red-500">*</span></label><input name="name" required value="{{ old('name', $activity->name ?? '') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    <div class="md:col-span-2"><label class="block text-sm font-semibold">Description</label><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300">{{ old('description', $activity->description ?? '') }}</textarea></div>

    <div class="md:col-span-2 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div><h3 class="text-sm font-bold text-gray-800">Agents GUT affectés</h3><p class="text-xs text-gray-500">Recherchez puis cochez plusieurs agents.</p></div>
            <span id="selected-agents-count" class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">{{ count($selectedAgents) }} sélectionné(s)</span>
        </div>
        <div class="relative mt-3">
            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
            <input type="search" id="agent-search" placeholder="Rechercher un agent GUT..." autocomplete="off" class="w-full rounded-lg border-gray-300 pl-10">
        </div>
        <div id="agent-options" class="mt-3 max-h-56 space-y-1 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2">
            @foreach($agentChoices as $agentName)
                <label class="agent-option flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition-colors hover:bg-sky-50" data-search="{{ mb_strtolower($agentName) }}">
                    <input type="checkbox" name="assigned_agents[]" value="{{ $agentName }}" @checked(in_array($agentName, $selectedAgents, true)) class="h-4 w-4 rounded border-gray-300 accent-sky-600 focus:ring-gut-blue">
                    <span class="text-sm text-gray-700">{{ $agentName }}</span>
                </label>
            @endforeach
            <p id="no-agent-result" class="{{ $agentChoices->isNotEmpty() ? 'hidden' : '' }} px-3 py-4 text-center text-sm text-gray-500">Aucun agent ne correspond à cette recherche.</p>
        </div>
    </div>

    <div class="md:col-span-2 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h3 class="text-sm font-bold text-gray-800">Parties prenantes externes</h3><p class="text-xs text-gray-500">Ajoutez les personnes externes impliquées dans cette activité.</p></div>
            <button type="button" id="add-external-stakeholder" class="min-h-11 cursor-pointer rounded-lg border border-gut-blue bg-white px-4 py-2 text-sm font-semibold text-gut-blue transition-colors hover:bg-sky-50"><i class="fas fa-plus mr-2" aria-hidden="true"></i>Ajouter une personne</button>
        </div>
        <div id="external-stakeholders-container" class="mt-4 space-y-3">
            @foreach($externalStakeholders as $index => $stakeholder)
                <div class="external-stakeholder-row rounded-lg border border-gray-200 bg-white p-4">
                    <div class="grid gap-3 md:grid-cols-3">
                        <div><label class="text-xs font-semibold text-gray-600">Nom <span class="text-red-500">*</span></label><input aria-label="Nom de la partie prenante externe" name="external_stakeholders[{{ $index }}][last_name]" required value="{{ $stakeholder['last_name'] ?? '' }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                        <div><label class="text-xs font-semibold text-gray-600">Prénom <span class="text-red-500">*</span></label><input aria-label="Prénom de la partie prenante externe" name="external_stakeholders[{{ $index }}][first_name]" required value="{{ $stakeholder['first_name'] ?? '' }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                        <div><label class="text-xs font-semibold text-gray-600">Email <span class="font-normal text-gray-400">(optionnel)</span></label><input aria-label="Email de la partie prenante externe" type="email" name="external_stakeholders[{{ $index }}][email]" value="{{ $stakeholder['email'] ?? '' }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                    </div>
                    <button type="button" class="remove-external-stakeholder mt-3 min-h-11 cursor-pointer rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition-colors hover:bg-red-50"><i class="fas fa-trash mr-2" aria-hidden="true"></i>Supprimer</button>
                </div>
            @endforeach
        </div>
    </div>

    <div><label class="block text-sm font-semibold">Début courant <span class="text-red-500">*</span></label><input type="date" name="current_start_date" required value="{{ old('current_start_date', isset($activity) ? $activity->current_start_date->format('Y-m-d') : '') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    <div><label class="block text-sm font-semibold">Fin courante <span class="text-red-500">*</span></label><input type="date" name="current_end_date" required value="{{ old('current_end_date', isset($activity) ? $activity->current_end_date->format('Y-m-d') : '') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    <div><label class="block text-sm font-semibold">Quantité prévue (%) <span class="text-red-500">*</span></label><input type="number" step="0.01" min="0.01" max="100" name="planned_quantity" required value="{{ old('planned_quantity', $activity->planned_quantity ?? 100) }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    <div><label class="block text-sm font-semibold">Priorité</label><select name="priority" class="mt-1 w-full rounded-lg border-gray-300">@foreach(['low'=>'Faible','normal'=>'Normale','high'=>'Haute','critical'=>'Critique'] as $value=>$label)<option value="{{ $value }}" @selected(old('priority', $activity->priority ?? 'normal') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="md:col-span-2"><label class="block text-sm font-semibold">Livrable attendu</label><input name="deliverable" value="{{ old('deliverable', $activity->deliverable ?? '') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
    @if(isset($activity))
        <div><label class="block text-sm font-semibold">Statut</label><select name="status" class="mt-1 w-full rounded-lg border-gray-300">@foreach(['not_started'=>'Non démarrée','in_progress'=>'En cours','completed'=>'Terminée','suspended'=>'Suspendue','blocked'=>'Bloquée'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', $activity->status) === $value)>{{ $label }}</option>@endforeach</select></div>
    @endif
    @if($projectTracking->baseline_approved_at)
        <div class="md:col-span-2"><label class="block text-sm font-semibold text-amber-800">Justification de la révision <span class="text-red-500">*</span></label><textarea name="change_reason" required rows="2" class="mt-1 w-full rounded-lg border-amber-300" placeholder="Pourquoi le planning courant est-il modifié ?">{{ old('change_reason') }}</textarea></div>
    @endif
</div>

@once
    @push('scripts')
    <script>
        const agentSearchInput = document.getElementById('agent-search');
        const agentOptions = [...document.querySelectorAll('.agent-option')];
        const noAgentResult = document.getElementById('no-agent-result');
        const selectedAgentsCount = document.getElementById('selected-agents-count');

        function updateSelectedAgentsCount() {
            const selectedCount = document.querySelectorAll('input[name="assigned_agents[]"]:checked').length;
            if (selectedAgentsCount) selectedAgentsCount.textContent = `${selectedCount} sélectionné(s)`;
        }

        agentSearchInput?.addEventListener('input', () => {
            const searchTerm = agentSearchInput.value.toLocaleLowerCase('fr').trim();
            let visibleCount = 0;
            agentOptions.forEach((option) => {
                const isVisible = option.dataset.search.includes(searchTerm);
                option.classList.toggle('hidden', !isVisible);
                visibleCount += isVisible ? 1 : 0;
            });
            noAgentResult?.classList.toggle('hidden', visibleCount > 0);
        });

        agentOptions.forEach((option) => option.querySelector('input')?.addEventListener('change', updateSelectedAgentsCount));

        const externalStakeholdersContainer = document.getElementById('external-stakeholders-container');
        const addExternalStakeholderButton = document.getElementById('add-external-stakeholder');
        let externalStakeholderIndex = {{ count($externalStakeholders) }};

        function bindExternalStakeholderRemoval(scope = document) {
            scope.querySelectorAll('.remove-external-stakeholder').forEach((button) => {
                if (button.dataset.bound === 'true') return;
                button.dataset.bound = 'true';
                button.addEventListener('click', () => button.closest('.external-stakeholder-row')?.remove());
            });
        }

        addExternalStakeholderButton?.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'external-stakeholder-row rounded-lg border border-gray-200 bg-white p-4';
            row.innerHTML = `
                <div class="grid gap-3 md:grid-cols-3">
                    <div><label class="text-xs font-semibold text-gray-600">Nom <span class="text-red-500">*</span></label><input aria-label="Nom de la partie prenante externe" name="external_stakeholders[${externalStakeholderIndex}][last_name]" required class="mt-1 w-full rounded-lg border-gray-300"></div>
                    <div><label class="text-xs font-semibold text-gray-600">Prénom <span class="text-red-500">*</span></label><input aria-label="Prénom de la partie prenante externe" name="external_stakeholders[${externalStakeholderIndex}][first_name]" required class="mt-1 w-full rounded-lg border-gray-300"></div>
                    <div><label class="text-xs font-semibold text-gray-600">Email <span class="font-normal text-gray-400">(optionnel)</span></label><input aria-label="Email de la partie prenante externe" type="email" name="external_stakeholders[${externalStakeholderIndex}][email]" class="mt-1 w-full rounded-lg border-gray-300"></div>
                </div>
                <button type="button" class="remove-external-stakeholder mt-3 min-h-11 cursor-pointer rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition-colors hover:bg-red-50"><i class="fas fa-trash mr-2" aria-hidden="true"></i>Supprimer</button>
            `;
            externalStakeholdersContainer?.appendChild(row);
            bindExternalStakeholderRemoval(row);
            row.querySelector('input')?.focus();
            externalStakeholderIndex++;
        });

        bindExternalStakeholderRemoval();
        updateSelectedAgentsCount();
    </script>
    @endpush
@endonce
