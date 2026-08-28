<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanRevision extends Model
{
    /** @use HasFactory<\Database\Factories\PlanRevisionFactory> */
    use HasFactory;

    protected $fillable = [
        'project_tracking_id',
        'project_activity_id',
        'user_id',
        'version',
        'reason',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
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
