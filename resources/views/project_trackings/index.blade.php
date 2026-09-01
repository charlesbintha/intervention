@extends('layouts.app')

@section('title', 'Suivi des travaux')

@section('content')
<div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Suivi des travaux</h1>
            <p class="text-gray-600 mt-1">Planning d’exécution et avancement des équipes terrain</p>
        </div>
        <a href="{{ route('project-trackings.create') }}" class="inline-flex items-center justify-center rounded-lg bg-gut-blue px-5 py-3 font-semibold text-white hover:opacity-90">
            <i class="fas fa-plus mr-2"></i>Nouveau suivi
        </a>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($projectTrackings as $tracking)
            <a href="{{ route('project-trackings.show', $tracking) }}" class="block rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wide text-gut-blue">{{ $tracking->external_project_code }}</span>
                        <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $tracking->external_project_name }}</h2>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tracking->status === 'active' ? 'bg-green-100 text-green-700' : ($tracking->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ ['draft' => 'Brouillon', 'active' => 'Actif', 'suspended' => 'Suspendu', 'completed' => 'Terminé'][$tracking->status] }}
                    </span>
                </div>
                <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                    <span>{{ $tracking->subsidiary }}</span>
                    <span>{{ $tracking->activities_count }} activité(s)</span>
                </div>
                <div class="mt-5">
                    <div class="mb-2 flex justify-between text-sm"><span>Avancement réel</span><strong>{{ $tracking->actual_progress }} %</strong></div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200"><div class="h-full bg-gut-blue" style="width: {{ $tracking->actual_progress }}%"></div></div>
                </div>
                <div class="mt-4 text-xs text-gray-500">{{ $tracking->work_logs_count }} déclaration(s)</div>
            </a>
        @empty
            <div class="col-span-full rounded-xl border-2 border-dashed border-gray-300 bg-white p-12 text-center">
                <i class="fas fa-list-check text-4xl text-gray-300"></i>
                <p class="mt-4 font-semibold text-gray-700">Aucun suivi de travaux pour le moment.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $projectTrackings->links() }}</div>
</div>
@endsection
