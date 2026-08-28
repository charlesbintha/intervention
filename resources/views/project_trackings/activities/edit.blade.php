@extends('layouts.app')

@section('title', 'Modifier une activité')

@section('content')
@php($projectTracking = $activity->projectTracking)
<div class="tracking-ui max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <a href="{{ route('project-trackings.show', $projectTracking) }}" class="text-sm font-semibold text-gut-blue">← Retour au suivi</a>
    <h1 class="my-6 text-3xl font-bold">Modifier l’activité</h1>
    <form action="{{ route('project-activities.update', $activity) }}" method="POST" class="space-y-6 rounded-xl bg-white p-8 shadow-sm">
        @csrf @method('PUT')
        @if($errors->any())<div class="rounded-lg bg-red-50 p-4 text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @include('project_trackings.activities._fields')
        <div class="flex justify-end"><button class="rounded-lg bg-gut-blue px-6 py-3 font-semibold text-white">Enregistrer les modifications</button></div>
    </form>
</div>
@endsection
