@props(['model'])
@php
    $hasEditorialRevision = \App\Models\TextRevision::query()->where('reviewable_type', $model::class)->where('reviewable_id', $model->getKey())->where('after_validation', true)->exists();
@endphp
@if($hasEditorialRevision)
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="note">
        Ce document comporte des corrections éditoriales après validation. La validation et les éventuelles signatures concernent la version originale, conservée dans l’historique des corrections IA.
    </div>
@endif
