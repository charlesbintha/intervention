@extends('layouts.app')

@section('title', 'Modifier un travail réalisé')

@section('content')
<div class="tracking-ui mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
    <a href="{{ route('project-trackings.show', $projectTracking) }}" class="text-sm font-semibold text-gut-blue">← Retour au suivi</a>
    <h1 class="my-6 text-3xl font-bold">Modifier le travail réalisé</h1>

    <form action="{{ route('project-trackings.work-logs.update', [$projectTracking, $workLog]) }}" method="POST" class="grid gap-5 rounded-xl bg-white p-8 shadow-sm md:grid-cols-2">
        @csrf
        @method('PUT')

        @if(session('error'))
            <div class="rounded-lg bg-red-50 p-4 text-red-700 md:col-span-2">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-4 text-red-700 md:col-span-2"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="md:col-span-2">
            <label for="project_activity_id" class="text-sm font-semibold">Activité <span class="text-red-500">*</span></label>
            <select id="project_activity_id" name="project_activity_id" required class="mt-1 w-full rounded-lg border-gray-300">
                @foreach($projectTracking->activities as $activity)
                    <option value="{{ $activity->id }}" @selected((string) old('project_activity_id', $workLog->project_activity_id) === (string) $activity->id)>{{ $activity->lot_name }} — {{ $activity->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label for="started_at" class="text-sm font-semibold">Début des travaux <span class="text-red-500">*</span></label><input id="started_at" type="datetime-local" name="started_at" max="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('started_at', $startedAt->format('Y-m-d\TH:i')) }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
        <div><label for="ended_at" class="text-sm font-semibold">Fin des travaux <span class="text-red-500">*</span></label><input id="ended_at" type="datetime-local" name="ended_at" value="{{ old('ended_at', $endedAt->format('Y-m-d\TH:i')) }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
        <div><label for="quantity_completed" class="text-sm font-semibold">Quantité réalisée (%) <span class="text-red-500">*</span></label><input id="quantity_completed" type="number" name="quantity_completed" min="0.01" max="100" step="0.01" value="{{ old('quantity_completed', $workLog->quantity_completed) }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
        <div><label for="remaining_quantity_estimate" class="text-sm font-semibold">Quantité restante estimée (%)</label><input id="remaining_quantity_estimate" type="number" name="remaining_quantity_estimate" min="0" step="0.01" value="{{ old('remaining_quantity_estimate', $workLog->remaining_quantity_estimate) }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
        <div class="md:col-span-2"><label for="work_description" class="text-sm font-semibold">Travaux réalisés <span class="text-red-500">*</span></label><textarea id="work_description" name="work_description" required rows="4" class="mt-1 w-full rounded-lg border-gray-300">{{ old('work_description', $workLog->work_description) }}</textarea></div>
        <div class="md:col-span-2"><label for="difficulties" class="text-sm font-semibold">Difficultés rencontrées</label><textarea id="difficulties" name="difficulties" rows="3" class="mt-1 w-full rounded-lg border-gray-300">{{ old('difficulties', $workLog->difficulties) }}</textarea></div>

        <div class="flex justify-end gap-3 border-t pt-5 md:col-span-2">
            <a href="{{ route('project-trackings.show', $projectTracking) }}" class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700">Annuler</a>
            <button class="cursor-pointer rounded-lg bg-gut-blue px-6 py-3 font-semibold text-white">Enregistrer les modifications</button>
        </div>
    </form>
</div>
@endsection
