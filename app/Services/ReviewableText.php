<?php

namespace App\Services;

use App\Models\InterventionUte;
use App\Models\Maintenance;
use App\Models\ProjectAction;
use App\Models\ProjectActivity;
use App\Models\ProjectBlocker;
use App\Models\ProjectTracking;
use App\Models\Survey;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Model;

class ReviewableText
{
    /** @return array<string, class-string<Model>> */
    public static function types(): array
    {
        return [
            'surveys' => Survey::class,
            'maintenances' => Maintenance::class,
            'intervention-utes' => InterventionUte::class,
            'project-trackings' => ProjectTracking::class,
            'project-activities' => ProjectActivity::class,
            'work-logs' => WorkLog::class,
            'project-blockers' => ProjectBlocker::class,
            'project-actions' => ProjectAction::class,
        ];
    }

    /** @return list<string> */
    public static function fields(string $type): array
    {
        return match ($type) {
            'surveys', 'maintenances' => ['purpose', 'layout_content'],
            'intervention-utes' => ['purpose', 'observations'],
            'project-trackings' => ['description'],
            'project-activities' => ['name', 'description', 'deliverable'],
            'work-logs' => ['work_description', 'difficulties'],
            'project-blockers' => ['description', 'impact', 'proposed_solution'],
            'project-actions' => ['title', 'description', 'completion_comment'],
            default => [],
        };
    }

    public static function type(Model $model): string
    {
        return array_search($model::class, self::types(), true) ?: '';
    }

    public static function canEdit(User $user, Model $model): bool
    {
        if ($model instanceof ProjectTracking) {
            return $user->can('update', $model);
        }

        if ($model instanceof ProjectActivity || $model instanceof WorkLog || $model instanceof ProjectBlocker || $model instanceof ProjectAction) {
            return $user->can('update', $model->projectTracking);
        }

        return $user->isAdmin() || (int) $model->user_id === (int) $user->id;
    }

    public static function hasContent(?string $value): bool
    {
        if (blank($value)) {
            return false;
        }

        $delta = json_decode($value, true);
        if (is_array($delta) && isset($delta['ops']) && is_array($delta['ops'])) {
            foreach ($delta['ops'] as $op) {
                if (isset($op['insert']) && (is_array($op['insert']) || preg_match('/[^\s\x{00A0}\x{200B}]/u', $op['insert']))) {
                    return true;
                }
            }

            return false;
        }

        return (bool) preg_match('/[^\s\x{00A0}\x{200B}]/u', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            || (bool) preg_match('/<(img|video|iframe)\b/i', $value);
    }

    public static function plainText(string $value): string
    {
        $delta = json_decode($value, true);
        if (is_array($delta) && isset($delta['ops'])) {
            return implode('', array_map(fn (array $op): string => is_string($op['insert'] ?? null) ? $op['insert'] : '[Pièce intégrée]', $delta['ops']));
        }

        return html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
