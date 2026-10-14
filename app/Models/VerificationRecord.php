<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerificationRecord extends Model
{
    protected $fillable = ['verifiable_type', 'verifiable_id', 'type', 'status', 'reviewed_by', 'notes', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
