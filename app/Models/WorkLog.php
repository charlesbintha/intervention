<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkLog extends Model
{
    /** @use HasFactory<\Database\Factories\WorkLogFactory> */
    use HasFactory;

    protected $fillable = [
        'project_tracking_id',
        'project_activity_id',
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'started_at',
        'ended_at',
        'quantity_completed',
        'remaining_quantity_estimate',
        'work_description',
        'difficulties',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'quantity_completed' => 'decimal:2',
            'remaining_quantity_estimate' => 'decimal:2',
        ];
    }

    public function projectTracking(): BelongsTo
    {
        return $this->belongsTo(ProjectTracking::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ProjectActivity::class, 'project_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
