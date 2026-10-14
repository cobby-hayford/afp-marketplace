<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileRole extends Model
{
    public $timestamps = false;

    protected $fillable = ['profile_id', 'role'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
