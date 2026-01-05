<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InterventionUte extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'location',
        'contact_name',
        'contact_function',
        'contact_phone',
        'contact_email',
        'start_datetime',
        'end_datetime',
        'purpose',
        'diagnostic',
        'type',
        'observations',
        'status',
        'subsidiary',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function intervenants(): MorphMany
    {
        return $this->morphMany(Intervenant::class, 'interventionable');
    }

    public function intervenantsGut(): MorphMany
    {
        return $this->morphMany(Intervenant::class, 'interventionable')
            ->where('type', 'gut');
    }

    public function intervenantsRencontres(): MorphMany
    {
        return $this->morphMany(Intervenant::class, 'interventionable')
            ->where('type', 'rencontre');
    }

    public function signature(): MorphOne
    {
        return $this->morphOne(Signature::class, 'signable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
