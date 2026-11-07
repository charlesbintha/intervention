<?php

namespace App\Observers;

use App\Models\TextRevision;
use App\Services\ReviewableText;
use App\Services\TextReviewService;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TextReviewObserver
{
    public function saved(Model $model): void
    {
        if (! auth()->check() || request()->routeIs('text-reviews.apply') || ! is_array(request()->input('_ai_reviews'))) {
            return;
        }

        $type = ReviewableText::type($model);
        foreach (array_slice(request()->input('_ai_reviews'), 0, 6) as $token) {
            if (! is_string($token)) {
                continue;
            }
            try {
                $payload = app(TextReviewService::class)->unseal($token, checkExpiration: false);
            } catch (HttpException) {
                continue;
            }
            $field = $payload['field'];
            if ($payload['type'] !== $type || ! in_array($field, ReviewableText::fields($type), true)
                || ($payload['id'] !== null && (int) $payload['id'] !== (int) $model->getKey())
                || (string) $model->getAttribute($field) !== $payload['corrected']
                || (! $model->wasRecentlyCreated && ! $model->wasChanged($field))) {
                continue;
            }

            if (! ReviewableText::canEdit(auth()->user(), $model)) {
                continue;
            }

            TextRevision::query()->create([
                'reviewable_type' => $model::class, 'reviewable_id' => $model->getKey(),
                'user_id' => auth()->id(), 'field' => $field,
                'original_text' => $payload['original'], 'corrected_text' => $payload['corrected'],
                'original_snapshot' => $model->wasRecentlyCreated ? array_replace($model->getAttributes(), [$field => $payload['original']]) : $model->getRawOriginal(),
                'model_name' => $payload['model_name'],
                'after_validation' => $model->getRawOriginal('status') === 'validated',
            ]);
        }
    }
}
