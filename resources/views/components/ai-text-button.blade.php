@props(['model', 'field'])

@if(config('text_review.enabled') && \App\Services\ReviewableText::canEdit(auth()->user(), $model) && \App\Services\ReviewableText::hasContent($model->getAttribute($field)))
    <span class="inline-flex items-center gap-1">
        <button type="button" data-ai-saved="{{ route('text-reviews.saved-preview', [\App\Services\ReviewableText::type($model), $model->getKey()]) }}"
            data-ai-apply="{{ route('text-reviews.apply', [\App\Services\ReviewableText::type($model), $model->getKey()]) }}" data-ai-field="{{ $field }}"
            title="Améliorer ce texte avec l’IA" aria-label="Améliorer ce texte avec l’IA"
            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700 hover:bg-sky-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
            <i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> IA
        </button>
        <button type="button" data-ai-history="{{ route('text-reviews.history', [\App\Services\ReviewableText::type($model), $model->getKey()]) }}"
            title="Historique des corrections IA" aria-label="Historique des corrections IA"
            class="rounded px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-sky-500"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></button>
    </span>
@endif
