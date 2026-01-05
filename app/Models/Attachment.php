<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Represents a file attached to a Survey, Maintenance or Intervention.
 */
class Attachment extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'original_name',
        'size',
        'mime_type',
        'uploaded_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
