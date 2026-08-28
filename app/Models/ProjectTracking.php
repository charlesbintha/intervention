<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTracking extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectTrackingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_project_code',
        'external_project_name',
        'external_opportunity_id',
        'subsidiary',
        'client_name',
        'location',
        'description',
        'baseline_start_date',
        'baseline_end_date',
        'current_start_date',
        'current_end_date',
        'status',
        'baseline_approved_at',
    ];

    protected $appends = [
        'actual_progress',
        'planned_progress',
    ];

    protected function casts(): array
    {
        return [
            'baseline_start_date' => 'date',
            'baseline_end_date' => 'date',
            'current_start_date' => 'date',
            'current_end_date' => 'date',
            'baseline_approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->orderBy('sort_order');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function blockers(): HasMany
    {
        return $this->hasMany(ProjectBlocker::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ProjectAction::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PlanRevision::class);
    }

    public function getActualProgressAttribute(): float
    {
        $activities = $this->relationLoaded('activities') ? $this->activities : $this->activities()->get();

        return $activities->isEmpty() ? 0.0 : round((float) $activities->avg('progress_percentage'), 1);
    }

    public function getPlannedProgressAttribute(): float
    {
        $activities = $this->relationLoaded('activities') ? $this->activities : $this->activities()->get();

        return $activities->isEmpty() ? 0.0 : round((float) $activities->avg('planned_progress_percentage'), 1);
    }
}
