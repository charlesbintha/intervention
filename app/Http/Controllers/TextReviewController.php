<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyTextReviewRequest;
use App\Http\Requests\ReviewTextRequest;
use App\Http\Requests\SavedTextReviewRequest;
use App\Jobs\SyncProjectActivityToPlanner;
use App\Models\ProjectActivity;
use App\Models\TextRevision;
use App\Services\ReviewableText;
use App\Services\TextReviewService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TextReviewController extends Controller
{
    public function __construct(private readonly TextReviewService $reviewService) {}

    public function preview(ReviewTextRequest $request): JsonResponse
    {
        $data = $request->validated();
        $type = $data['type'];
        $model = isset($data['id']) ? $this->resolve($type, (int) $data['id']) : null;
        $fields = $data['fields'];
        abort_if(array_diff(array_keys($fields), ReviewableText::fields($type)) !== [], 422, 'Ce champ ne peut pas être corrigé par l’IA.');
        abort_if(array_sum(array_map('mb_strlen', $fields)) > 24000, 422, 'Relisez les champs séparément pour limiter la longueur de la demande.');

        $results = [];
        try {
            $reviews = $this->reviewService->reviewFields($fields);
            foreach ($fields as $field => $value) {
                $result = $reviews[$field];
                $results[$field] = [
                    ...$result,
                    'original' => ReviewableText::plainText($value),
                    'corrected' => ReviewableText::plainText($result['value']),
                    'token' => $result['changed'] ? $this->reviewService->seal([
                        'type' => $type,
                        'id' => $model?->getKey(),
                        'field' => $field,
                        'original' => $value,
                        'corrected' => $result['value'],
                    ]) : null,
                ];
            }
        } catch (Throwable $exception) {
            Log::warning('Text review unavailable', ['exception_class' => $exception::class]);

            return response()->json(['message' => 'La relecture IA n’a pas abouti. Votre texte est conservé ; vous pouvez enregistrer sans correction.'], 503);
        }

        return response()->json(['fields' => $results]);
    }

    public function previewSaved(SavedTextReviewRequest $request, string $type, int $id): JsonResponse
    {
        $model = $this->resolve($type, $id);
        $field = $request->string('field')->toString();
        abort_unless(in_array($field, ReviewableText::fields($type), true), 422);
        $value = (string) $model->getAttribute($field);
        abort_if(mb_strlen($value) > 18000, 422, 'Ce texte dépasse 18 000 caractères. Relisez-le par sections dans le formulaire.');

        try {
            $result = $this->reviewService->reviewFields([$field => $value])[$field];
        } catch (Throwable $exception) {
            Log::warning('Saved text review unavailable', ['exception_class' => $exception::class]);

            return response()->json(['message' => 'La relecture IA n’a pas abouti. Le texte original a été conservé.'], 503);
        }

        return response()->json(['fields' => [$field => [
            ...$result,
            'original' => ReviewableText::plainText($value),
            'corrected' => ReviewableText::plainText($result['value']),
            'after_validation' => $model->getAttribute('status') === 'validated',
            'token' => $result['changed'] ? $this->reviewService->seal([
                'type' => $type, 'id' => $id, 'field' => $field,
                'original' => $value, 'corrected' => $result['value'],
            ]) : null,
        ]]]);
    }

    public function apply(ApplyTextReviewRequest $request, string $type, int $id): JsonResponse
    {
        $this->resolve($type, $id);
        $payload = $this->reviewService->unseal($request->string('token')->toString());
        abort_unless($payload['type'] === $type && (int) $payload['id'] === $id && in_array($payload['field'], ReviewableText::fields($type), true), 422);

        DB::transaction(function () use ($type, $id, $payload): void {
            $model = ReviewableText::types()[$type]::query()->lockForUpdate()->findOrFail($id);
            abort_unless(ReviewableText::canEdit(auth()->user(), $model), 403);
            abort_unless((string) $model->getAttribute($payload['field']) === $payload['original'], 409, 'Ce texte a changé depuis la relecture. Actualisez la page et relancez la correction.');
            $snapshot = $model->getAttributes();
            if (method_exists($model, 'signature')) {
                $snapshot['signature'] = $model->signature?->toArray();
            }

            TextRevision::query()->create([
                'reviewable_type' => $model::class, 'reviewable_id' => $id,
                'user_id' => auth()->id(), 'field' => $payload['field'],
                'original_text' => $payload['original'], 'corrected_text' => $payload['corrected'],
                'original_snapshot' => $snapshot, 'model_name' => $payload['model_name'],
                'after_validation' => $model->getAttribute('status') === 'validated',
            ]);

            $model->setAttribute($payload['field'], $payload['corrected']);
            $model->save();

            if ($model instanceof ProjectActivity && config('services.microsoft_graph.auto_sync')
                && filled($model->projectTracking->ms_group_id) && filled($model->projectTracking->ms_plan_id)) {
                $model->forceFill(['planner_sync_status' => 'pending', 'planner_sync_error' => null])->saveQuietly();
                SyncProjectActivityToPlanner::dispatch($model->id)->afterCommit();
            }
        });

        return response()->json(['message' => 'La correction a été appliquée et son original conservé dans l’historique.']);
    }

    public function history(string $type, int $id): JsonResponse
    {
        $model = $this->resolve($type, $id);
        $history = TextRevision::query()->where('reviewable_type', $model::class)->where('reviewable_id', $id)
            ->with('user:id,name')->latest('id')->paginate(20);

        return response()->json([
            'revisions' => $history->getCollection()->map(fn (TextRevision $revision): array => [
                'field' => $revision->field, 'author' => $revision->user?->name ?? 'Utilisateur supprimé',
                'date' => $revision->created_at->format('d/m/Y H:i'),
                'original' => ReviewableText::plainText($revision->original_text),
                'corrected' => ReviewableText::plainText($revision->corrected_text),
                'after_validation' => $revision->after_validation,
                'snapshot_url' => route('text-reviews.snapshot', [$type, $id, $revision->id]),
            ]),
            'next_page_url' => $history->nextPageUrl(),
        ]);
    }

    public function snapshot(string $type, int $id, int $revision): JsonResponse
    {
        $model = $this->resolve($type, $id);
        $revision = TextRevision::query()->where('reviewable_type', $model::class)->where('reviewable_id', $id)->findOrFail($revision);

        return response()->json($revision->original_snapshot)->header('Content-Disposition', 'attachment; filename="original-'.$id.'-'.$revision->id.'.json"');
    }

    private function resolve(string $type, int $id): Model
    {
        $class = ReviewableText::types()[$type] ?? null;
        abort_unless($class, 404);
        $model = $class::query()->findOrFail($id);
        abort_unless(ReviewableText::canEdit(auth()->user(), $model), 403);

        return $model;
    }
}
