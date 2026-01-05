<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Intervenant extends Model
{
    protected $fillable = [
        'interventionable_id',
        'interventionable_type',
        'type',
        'source',
        'nom',
        'prenom',
        'email',
        'telephone',
        'api_id',
    ];

    public function interventionable(): MorphTo
    {
        return $this->morphTo();
    }
}
