<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBlocker extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectBlockerFactory> */
    use HasFactory;

    protected $fillable = [
        'project_tracking_id',
        'project_activity_id',
        'user_id',
        'category',
        'description',
        'severity',
        'impact',
        'proposed_solution',
        'status',
        'opened_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'resolved_at' => 'date',
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
