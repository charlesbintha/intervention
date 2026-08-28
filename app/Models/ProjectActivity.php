<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ProjectActivity extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectActivityFactory> */
    use HasFactory;

    protected $hidden = ['weight'];

    protected $fillable = [
        'project_tracking_id',
        'lot_name',
        'phase_name',
        'name',
        'description',
        'assigned_agents',
        'external_stakeholders',
        'baseline_start_date',
        'baseline_end_date',
        'current_start_date',
        'current_end_date',
        'unit',
        'planned_quantity',
        'completed_quantity',
        'status',
        'deliverable',
        'priority',
        'sort_order',
    ];

    protected $appends = [
        'progress_percentage',
        'planned_progress_percentage',
    ];

    protected function casts(): array
    {
        return [
            'assigned_agents' => 'array',
            'external_stakeholders' => 'array',
            'baseline_start_date' => 'date',
            'baseline_end_date' => 'date',
            'current_start_date' => 'date',
            'current_end_date' => 'date',
            'planned_quantity' => 'decimal:2',
            'completed_quantity' => 'decimal:2',
        ];
    }

    public function projectTracking(): BelongsTo
    {
        return $this->belongsTo(ProjectTracking::class);
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

    public function getProgressPercentageAttribute(): float
    {
        if ((float) $this->planned_quantity <= 0) {
            return $this->status === 'completed' ? 100.0 : 0.0;
        }

        return round(min(100, ((float) $this->completed_quantity / (float) $this->planned_quantity) * 100), 1);
    }

    public function getPlannedProgressPercentageAttribute(): float
    {
        $start = $this->current_start_date;
        $end = $this->current_end_date;
        $today = Carbon::today();

        if ($today->lt($start)) {
            return 0.0;
        }

        if ($today->gte($end)) {
            return 100.0;
        }

        $duration = max(1, $start->diffInDays($end));
        $elapsed = $start->diffInDays($today);

        return round(min(100, ($elapsed / $duration) * 100), 1);
    }
}
