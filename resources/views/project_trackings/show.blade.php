@extends('layouts.app')

@section('title', $projectTracking->external_project_name)

@section('content')
<div class="tracking-ui max-w-[1760px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('project-trackings.index') }}" class="text-sm font-semibold text-gut-blue">← Tous les suivis</a>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900">{{ $projectTracking->external_project_name }}</h1>
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">{{ $projectTracking->subsidiary }}</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ ['draft'=>'Brouillon','active'=>'Actif','suspended'=>'Suspendu','completed'=>'Terminé'][$projectTracking->status] }}</span>
            </div>
            <p class="mt-2 text-gray-600">{{ $projectTracking->external_project_code }} · {{ $projectTracking->client_name ?: 'Client non renseigné' }} · {{ $projectTracking->location ?: 'Site non renseigné' }}</p>
        </div>
        @can('update', $projectTracking)
            <div class="flex gap-3">
                <a href="{{ route('project-trackings.edit', $projectTracking) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700">Modifier</a>
                <form action="{{ route('project-trackings.destroy', $projectTracking) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce suivi et toutes ses données ?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-4 py-2 font-semibold text-red-700">Supprimer</button></form>
            </div>
        @endcan
    </div>

    @if($errors->any())<div class="rounded-lg bg-red-50 p-4 text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Avancement réel</p><p class="mt-1 text-3xl font-bold text-gut-blue">{{ $projectTracking->actual_progress }}%</p></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Avancement prévu</p><p class="mt-1 text-3xl font-bold text-gray-800">{{ $projectTracking->planned_progress }}%</p></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Écart</p><p class="mt-1 text-3xl font-bold {{ $projectTracking->actual_progress - $projectTracking->planned_progress < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($projectTracking->actual_progress - $projectTracking->planned_progress, 1) }}</p></div>
    </div>

    <section class="rounded-xl bg-white shadow-sm overflow-hidden">
        <div class="flex flex-col gap-4 border-b p-6 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-xl font-bold">Planning d’exécution</h2><p class="text-sm text-gray-500">Baseline : {{ $projectTracking->baseline_approved_at ? 'validée le '.$projectTracking->baseline_approved_at->format('d/m/Y') : 'non validée' }}</p></div>
            @if(!$projectTracking->baseline_approved_at && auth()->user()->can('update', $projectTracking))
                <form action="{{ route('project-trackings.approve-baseline', $projectTracking) }}" method="POST">@csrf<button class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white" onclick="return confirm('Valider ce planning initial comme référence ?')">Valider ce planning V1</button></form>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Lot / Activité</th><th class="px-4 py-3 text-left">Période</th><th class="px-4 py-3 text-left">Intervenants</th><th class="px-4 py-3 text-right">Quantité réalisée (%)</th><th class="px-4 py-3 text-left">Avancement</th><th class="px-4 py-3"></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($projectTracking->activities as $activity)
                    <tr>
                        <td class="px-4 py-4"><p class="text-xs font-semibold uppercase text-gut-blue">{{ $activity->lot_name }}{{ $activity->phase_name ? ' · '.$activity->phase_name : '' }}</p><p class="font-semibold text-gray-900">{{ $activity->name }}</p><p class="text-xs text-gray-500">{{ ['not_started'=>'Non démarrée','in_progress'=>'En cours','completed'=>'Terminée','suspended'=>'Suspendue','blocked'=>'Bloquée'][$activity->status] }}</p></td>
                        <td class="px-4 py-4 whitespace-nowrap">{{ $activity->current_start_date->format('d/m/Y') }}<br>{{ $activity->current_end_date->format('d/m/Y') }}@if($activity->baseline_end_date && !$activity->baseline_end_date->equalTo($activity->current_end_date))<p class="text-xs text-orange-600">Initiale : {{ $activity->baseline_end_date->format('d/m/Y') }}</p>@endif</td>
                        <td class="px-4 py-4 text-gray-600"><p>{{ implode(', ', $activity->assigned_agents ?? []) ?: 'Aucun agent GUT' }}</p>@if(!empty($activity->external_stakeholders))<p class="mt-1 text-xs text-gray-500">Externes : {{ collect($activity->external_stakeholders)->map(fn ($stakeholder) => trim(($stakeholder['first_name'] ?? '').' '.($stakeholder['last_name'] ?? '')))->filter()->implode(', ') }}</p>@endif</td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">{{ number_format((float)$activity->completed_quantity, 2, ',', ' ') }} / {{ number_format((float)$activity->planned_quantity, 2, ',', ' ') }} %</td>
                        <td class="px-4 py-4 min-w-36"><div class="flex justify-between text-xs"><span>Réel</span><strong>{{ $activity->progress_percentage }}%</strong></div><div class="mt-1 h-2 rounded-full bg-gray-200"><div class="h-full rounded-full bg-gut-blue" style="width:{{ $activity->progress_percentage }}%"></div></div></td>
                        <td class="px-4 py-4">@can('update', $projectTracking)<a href="{{ route('project-activities.edit', $activity) }}" class="text-gut-blue" title="Modifier"><i class="fas fa-pen"></i></a>@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Aucune activité. Construisez le planning ci-dessous.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @can('update', $projectTracking)
            <details class="border-t p-6" {{ $projectTracking->activities->isEmpty() ? 'open' : '' }}>
                <summary class="cursor-pointer font-bold text-gut-blue">Ajouter une activité au planning</summary>
                <form action="{{ route('project-trackings.activities.store', $projectTracking) }}" method="POST" class="mt-6 space-y-5">@csrf @include('project_trackings.activities._fields', ['activity' => null])<div class="flex justify-end"><button class="rounded-lg bg-gut-blue px-5 py-2 font-semibold text-white">Ajouter l’activité</button></div></form>
            </details>
        @endcan
    </section>

    <section class="rounded-xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div><h2 class="text-xl font-bold">Travaux réalisés</h2><p class="mt-1 text-sm text-gray-500">Historique des déclarations terrain.</p></div>
            @can('update', $projectTracking)<button type="button" onclick="openTrackingModal('work-log-modal')" aria-label="Déclarer un travail réalisé" title="Déclarer un travail réalisé" class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-full bg-gut-blue text-lg text-white shadow-sm transition-colors hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2"><i class="fas fa-plus" aria-hidden="true"></i></button>@endcan
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse($projectTracking->workLogs as $log)
                <div class="rounded-lg border border-gray-200 p-4"><div class="flex justify-between gap-3"><div><p class="font-semibold">{{ $log->activity->name }}</p><p class="text-xs text-gray-500">Du {{ ($log->started_at ?? $log->work_date)->format('d/m/Y H:i') }} au {{ ($log->ended_at ?? $log->work_date)->format('d/m/Y H:i') }} · {{ $log->user->name }}</p></div>@can('update', $projectTracking)<form action="{{ route('project-trackings.work-logs.destroy', [$projectTracking, $log]) }}" method="POST" onsubmit="return confirm('Supprimer cette déclaration ?')">@csrf @method('DELETE')<button aria-label="Supprimer cette déclaration" class="cursor-pointer text-red-500"><i class="fas fa-trash" aria-hidden="true"></i></button></form>@endcan</div><p class="mt-2 text-sm">{{ $log->work_description }}</p><p class="mt-2 text-sm font-semibold text-gut-blue">+ {{ number_format((float)$log->quantity_completed, 2, ',', ' ') }} %</p>@if($log->difficulties)<p class="mt-2 text-sm text-orange-700">Difficulté : {{ $log->difficulties }}</p>@endif</div>
            @empty<p class="md:col-span-2 xl:col-span-3 text-gray-500">Aucun travail déclaré.</p>@endforelse
        </div>
    </section>

    <section class="rounded-xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div><h2 class="text-xl font-bold">Plan d’actions</h2><p class="mt-1 text-sm text-gray-500">Liste des actions à réaliser et de leurs responsables.</p></div>
            @can('update', $projectTracking)<button type="button" onclick="openTrackingModal('action-modal')" aria-label="Ajouter une action" title="Ajouter une action" class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-full bg-gut-blue text-lg text-white shadow-sm transition-colors hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2"><i class="fas fa-plus" aria-hidden="true"></i></button>@endcan
        </div>
        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse($projectTracking->actions as $action)
                <div class="rounded-lg border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-semibold">{{ $action->title }}</p>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="text-xs {{ $action->due_date->isPast() && $action->status !== 'completed' ? 'font-bold text-red-600' : 'text-gray-500' }}">{{ $action->due_date->format('d/m/Y') }}</span>
                            @can('update', $projectTracking)
                                <form action="{{ route('project-actions.destroy', $action) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cette action ?')">@csrf @method('DELETE')<button type="submit" aria-label="Supprimer cette action" title="Supprimer cette action" class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition-colors hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"><i class="fas fa-trash" aria-hidden="true"></i></button></form>
                            @endcan
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Responsables : {{ collect($action->responsible_names ?: [$action->responsible_name])->filter()->implode(', ') }}</p>
                    @can('update', $projectTracking)
                        <form action="{{ route('project-actions.status', $action) }}" method="POST" class="mt-3 flex flex-col gap-2 sm:flex-row">@csrf @method('PATCH')<select name="status" class="rounded-lg border-gray-300 text-sm">@foreach(['open'=>'Ouverte','in_progress'=>'En cours','completed'=>'Terminée','cancelled'=>'Annulée'] as $value=>$label)<option value="{{ $value }}" @selected($action->status === $value)>{{ $label }}</option>@endforeach</select><button class="cursor-pointer rounded-lg bg-gray-800 px-3 py-2 text-sm text-white">Mettre à jour</button></form>
                    @else
                        <span class="mt-3 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ['open'=>'Ouverte','in_progress'=>'En cours','completed'=>'Terminée','cancelled'=>'Annulée'][$action->status] }}</span>
                    @endcan
                </div>
            @empty
                <p class="text-gray-500 md:col-span-2 xl:col-span-3">Aucune action.</p>
            @endforelse
        </div>
    </section>

    @can('update', $projectTracking)
    <dialog id="work-log-modal" aria-labelledby="work-log-modal-title" class="w-[min(92vw,56rem)] max-h-[90vh] rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/60">
        <div class="flex max-h-[90vh] flex-col bg-white">
            <div class="flex items-center justify-between border-b px-6 py-5"><div><h2 id="work-log-modal-title" class="text-xl font-bold">Déclarer le travail réalisé</h2><p class="mt-1 text-sm text-gray-500">Renseignez le travail réellement effectué sur le terrain.</p></div><button type="button" onclick="closeTrackingModal('work-log-modal')" aria-label="Fermer" class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"><i class="fas fa-times" aria-hidden="true"></i></button></div>
            <form action="{{ route('project-trackings.work-logs.store', $projectTracking) }}" method="POST" class="grid gap-4 overflow-y-auto p-6 md:grid-cols-2">@csrf <input type="hidden" name="_form_context" value="work-log">
                <div class="md:col-span-2"><label for="work_activity" class="text-sm font-semibold">Activité <span class="text-red-500">*</span></label><select id="work_activity" name="project_activity_id" required class="mt-1 w-full rounded-lg border-gray-300"><option value="">Sélectionner</option>@foreach($projectTracking->activities as $activity)<option value="{{ $activity->id }}" @selected(old('_form_context') === 'work-log' && (string) old('project_activity_id') === (string) $activity->id)>{{ $activity->lot_name }} — {{ $activity->name }}</option>@endforeach</select></div>
                <div><label for="started_at" class="text-sm font-semibold">Début des travaux <span class="text-red-500">*</span></label><input id="started_at" type="datetime-local" name="started_at" max="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('_form_context') === 'work-log' ? old('started_at', now()->format('Y-m-d\TH:i')) : now()->format('Y-m-d\TH:i') }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div><label for="ended_at" class="text-sm font-semibold">Fin des travaux <span class="text-red-500">*</span></label><input id="ended_at" type="datetime-local" name="ended_at" value="{{ old('_form_context') === 'work-log' ? old('ended_at') : '' }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div class="md:col-span-2"><label for="quantity_completed" class="text-sm font-semibold">Quantité réalisée (%) <span class="text-red-500">*</span></label><input id="quantity_completed" type="number" name="quantity_completed" min="0.01" max="100" step="0.01" value="{{ old('_form_context') === 'work-log' ? old('quantity_completed') : '' }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div class="md:col-span-2"><label for="work_description" class="text-sm font-semibold">Travaux réalisés <span class="text-red-500">*</span></label><textarea id="work_description" name="work_description" required rows="3" class="mt-1 w-full rounded-lg border-gray-300">{{ old('_form_context') === 'work-log' ? old('work_description') : '' }}</textarea></div>
                <div class="md:col-span-2"><label for="difficulties" class="text-sm font-semibold">Difficultés rencontrées</label><textarea id="difficulties" name="difficulties" rows="2" class="mt-1 w-full rounded-lg border-gray-300">{{ old('_form_context') === 'work-log' ? old('difficulties') : '' }}</textarea></div>
                <div class="md:col-span-2 flex justify-end gap-3 border-t pt-5"><button type="button" onclick="closeTrackingModal('work-log-modal')" class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Annuler</button><button class="cursor-pointer rounded-lg bg-gut-blue px-5 py-2 text-sm font-semibold text-white">Enregistrer la déclaration</button></div>
            </form>
        </div>
    </dialog>

    @php
        $selectedActionResponsibles = old('_form_context') === 'action' ? array_values(old('responsible_names', [])) : [];
        $actionResponsibleChoices = collect($employees)->pluck('prenom_nom')->filter()->merge($selectedActionResponsibles)->unique()->sort()->values();
    @endphp

    <dialog id="action-modal" aria-labelledby="action-modal-title" class="w-[min(92vw,56rem)] max-h-[90vh] rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/60">
        <div class="flex max-h-[90vh] flex-col bg-white">
            <div class="flex items-center justify-between border-b px-6 py-5"><div><h2 id="action-modal-title" class="text-xl font-bold">Ajouter une action</h2><p class="mt-1 text-sm text-gray-500">Attribuez une action claire à une ou plusieurs personnes.</p></div><button type="button" onclick="closeTrackingModal('action-modal')" aria-label="Fermer" class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"><i class="fas fa-times" aria-hidden="true"></i></button></div>
            <form action="{{ route('project-trackings.actions.store', $projectTracking) }}" method="POST" class="grid gap-4 overflow-y-auto p-6 md:grid-cols-2">@csrf <input type="hidden" name="_form_context" value="action">
                <div class="md:col-span-2"><label for="action_title" class="text-sm font-semibold">Action à réaliser <span class="text-red-500">*</span></label><input id="action_title" name="title" required value="{{ old('_form_context') === 'action' ? old('title') : '' }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div><label for="action_activity" class="text-sm font-semibold">Périmètre</label><select id="action_activity" name="project_activity_id" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Projet entier</option>@foreach($projectTracking->activities as $activity)<option value="{{ $activity->id }}" @selected(old('_form_context') === 'action' && (string) old('project_activity_id') === (string) $activity->id)>{{ $activity->name }}</option>@endforeach</select></div>
                <div><label for="due_date" class="text-sm font-semibold">Date limite <span class="text-red-500">*</span></label><input id="due_date" type="date" name="due_date" required value="{{ old('_form_context') === 'action' ? old('due_date') : '' }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div class="md:col-span-2 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div><h3 class="text-sm font-bold text-gray-800">Responsables <span class="text-red-500">*</span></h3><p class="text-xs text-gray-500">Recherchez puis cochez une ou plusieurs personnes.</p></div>
                        <span id="selected-action-responsibles-count" class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">{{ count($selectedActionResponsibles) }} sélectionné(s)</span>
                    </div>
                    <div class="relative mt-3">
                        <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
                        <input type="search" id="action-responsible-search" placeholder="Rechercher une personne..." autocomplete="off" class="w-full rounded-lg border-gray-300 pl-10">
                    </div>
                    <div id="action-responsible-options" class="mt-3 max-h-56 space-y-1 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2">
                        @foreach($actionResponsibleChoices as $responsibleName)
                            <label class="action-responsible-option flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition-colors hover:bg-sky-50" data-search="{{ mb_strtolower($responsibleName) }}">
                                <input type="checkbox" name="responsible_names[]" value="{{ $responsibleName }}" @checked(in_array($responsibleName, $selectedActionResponsibles, true)) class="h-4 w-4 rounded border-gray-300 accent-sky-600 focus:ring-gut-blue">
                                <span class="text-sm text-gray-700">{{ $responsibleName }}</span>
                            </label>
                        @endforeach
                        <p id="no-action-responsible-result" class="{{ $actionResponsibleChoices->isNotEmpty() ? 'hidden' : '' }} px-3 py-4 text-center text-sm text-gray-500">Aucune personne ne correspond à cette recherche.</p>
                    </div>
                </div>
                <div><label for="action_priority" class="text-sm font-semibold">Priorité <span class="text-red-500">*</span></label><select id="action_priority" name="priority" class="mt-1 w-full rounded-lg border-gray-300">@foreach(['normal'=>'Normale','high'=>'Haute','critical'=>'Critique','low'=>'Faible'] as $value => $label)<option value="{{ $value }}" @selected((old('_form_context') === 'action' ? old('priority', 'normal') : 'normal') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="md:col-span-2"><label for="action_description" class="text-sm font-semibold">Détails</label><textarea id="action_description" name="description" class="mt-1 w-full rounded-lg border-gray-300">{{ old('_form_context') === 'action' ? old('description') : '' }}</textarea></div>
                <div class="md:col-span-2 flex justify-end gap-3 border-t pt-5"><button type="button" onclick="closeTrackingModal('action-modal')" class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Annuler</button><button class="cursor-pointer rounded-lg bg-gut-blue px-5 py-2 text-sm font-semibold text-white">Ajouter l’action</button></div>
            </form>
        </div>
    </dialog>
    @endcan

    @if($projectTracking->revisions->isNotEmpty())
        <section class="rounded-xl bg-white p-6 shadow-sm"><h2 class="text-xl font-bold">Historique des révisions</h2><div class="mt-4 space-y-3">@foreach($projectTracking->revisions as $revision)<div class="flex gap-4 border-l-4 border-gut-blue bg-gray-50 p-4"><span class="font-bold text-gut-blue">V{{ $revision->version }}</span><div><p class="font-semibold">{{ $revision->reason }}</p><p class="text-xs text-gray-500">{{ $revision->created_at->format('d/m/Y H:i') }} par {{ $revision->user->name }}{{ $revision->activity ? ' · '.$revision->activity->name : '' }}</p></div></div>@endforeach</div></section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function openTrackingModal(id) {
        const modal = document.getElementById(id);

        if (modal && !modal.open) {
            modal.showModal();
            document.body.style.overflow = 'hidden';
        }
    }

    function closeTrackingModal(id) {
        const modal = document.getElementById(id);

        if (modal?.open) {
            modal.close();
            document.body.style.overflow = '';
        }
    }

    document.querySelectorAll('dialog').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeTrackingModal(modal.id);
            }
        });

        modal.addEventListener('close', () => {
            document.body.style.overflow = '';
        });
    });

    const invalidFormContext = @js(old('_form_context'));
    const modalByFormContext = {
        'work-log': 'work-log-modal',
        action: 'action-modal',
    };

    if (modalByFormContext[invalidFormContext]) {
        openTrackingModal(modalByFormContext[invalidFormContext]);
    }

    const startedAtInput = document.getElementById('started_at');
    const endedAtInput = document.getElementById('ended_at');

    function syncWorkEndMinimum() {
        if (!startedAtInput?.value || !endedAtInput) {
            return;
        }

        const minimumEnd = new Date(startedAtInput.value);
        minimumEnd.setMinutes(minimumEnd.getMinutes() + 1);
        const localMinimumEnd = new Date(minimumEnd.getTime() - minimumEnd.getTimezoneOffset() * 60000)
            .toISOString()
            .slice(0, 16);

        endedAtInput.min = localMinimumEnd;
    }

    startedAtInput?.addEventListener('change', syncWorkEndMinimum);
    syncWorkEndMinimum();

    const actionResponsibleSearch = document.getElementById('action-responsible-search');
    const actionResponsibleOptions = [...document.querySelectorAll('.action-responsible-option')];
    const noActionResponsibleResult = document.getElementById('no-action-responsible-result');
    const selectedActionResponsiblesCount = document.getElementById('selected-action-responsibles-count');

    function updateSelectedActionResponsiblesCount() {
        const selectedCount = document.querySelectorAll('input[name="responsible_names[]"]:checked').length;
        if (selectedActionResponsiblesCount) selectedActionResponsiblesCount.textContent = `${selectedCount} sélectionné(s)`;
    }

    actionResponsibleSearch?.addEventListener('input', () => {
        const searchTerm = actionResponsibleSearch.value.toLocaleLowerCase('fr').trim();
        let visibleCount = 0;

        actionResponsibleOptions.forEach((option) => {
            const isVisible = option.dataset.search.includes(searchTerm);
            option.classList.toggle('hidden', !isVisible);
            visibleCount += isVisible ? 1 : 0;
        });

        noActionResponsibleResult?.classList.toggle('hidden', visibleCount > 0);
    });

    actionResponsibleOptions.forEach((option) => option.querySelector('input')?.addEventListener('change', updateSelectedActionResponsiblesCount));
    updateSelectedActionResponsiblesCount();
</script>
@endpush
