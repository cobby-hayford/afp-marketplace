<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileDocument extends Model
{
    protected $fillable = ['profile_id', 'type', 'title', 'path', 'mime_type', 'size'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
