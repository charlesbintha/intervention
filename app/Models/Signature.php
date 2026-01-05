<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Signature extends Model
{
    protected $fillable = [
        'signable_id',
        'signable_type',
        'signature_data',
        'signer_name',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }
}
