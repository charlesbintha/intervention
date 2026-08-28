<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAction extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectActionFactory> */
    use HasFactory;

    protected $fillable = [
        'project_tracking_id',
        'project_activity_id',
        'user_id',
        'title',
        'description',
        'responsible_name',
        'due_date',
        'priority',
        'status',
        'completion_comment',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
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
