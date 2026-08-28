@extends('layouts.app')

@section('title', 'Modifier le suivi')

@section('content')
<div class="tracking-ui max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <a href="{{ route('project-trackings.show', $projectTracking) }}" class="text-sm font-semibold text-gut-blue">← Retour au projet</a>
    <h1 class="my-6 text-3xl font-bold text-gray-900">Modifier {{ $projectTracking->external_project_name }}</h1>
    <form action="{{ route('project-trackings.update', $projectTracking) }}" method="POST" class="space-y-6 rounded-xl bg-white p-8 shadow-sm">
        @csrf @method('PUT')
        @if($errors->any())<div class="rounded-lg bg-red-50 p-4 text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div><label class="block text-sm font-semibold">Filiale</label><select name="subsidiary" required class="mt-2 w-full rounded-lg border-gray-300">@foreach(['GUT','CP','UTA','UA','UTE','UC'] as $code)<option value="{{ $code }}" @selected(old('subsidiary', $projectTracking->subsidiary) === $code)>{{ $code }}</option>@endforeach</select></div>
        <div class="grid gap-5 md:grid-cols-2">
            <div><label class="block text-sm font-semibold">Client du projet</label><input value="{{ $projectTracking->client_name ?: 'Non renseigné dans le projet' }}" readonly class="mt-2 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 py-2.5 text-gray-700"></div>
            <div><label class="block text-sm font-semibold">Localisation</label><input name="location" value="{{ old('location', $projectTracking->location) }}" class="mt-2 w-full rounded-lg border-gray-300"></div>
            <div><label class="block text-sm font-semibold">Début courant</label><input type="date" name="current_start_date" value="{{ old('current_start_date', $projectTracking->current_start_date?->format('Y-m-d')) }}" @disabled($projectTracking->baseline_approved_at) class="mt-2 w-full rounded-lg border-gray-300 disabled:bg-gray-100"></div>
            <div><label class="block text-sm font-semibold">Fin courante</label><input type="date" name="current_end_date" value="{{ old('current_end_date', $projectTracking->current_end_date?->format('Y-m-d')) }}" @disabled($projectTracking->baseline_approved_at) class="mt-2 w-full rounded-lg border-gray-300 disabled:bg-gray-100"></div>
        </div>
        <div><label class="block text-sm font-semibold">Statut</label><select name="status" class="mt-2 w-full rounded-lg border-gray-300">@foreach(['draft'=>'Brouillon','active'=>'Actif','suspended'=>'Suspendu','completed'=>'Terminé'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', $projectTracking->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-semibold">Description</label><textarea name="description" rows="4" class="mt-2 w-full rounded-lg border-gray-300">{{ old('description', $projectTracking->description) }}</textarea></div>
        <div class="flex justify-end"><button class="rounded-lg bg-gut-blue px-6 py-3 font-semibold text-white">Enregistrer</button></div>
    </form>
</div>
@endsection
