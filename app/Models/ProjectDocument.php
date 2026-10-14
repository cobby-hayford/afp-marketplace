<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocument extends Model
{
    protected $fillable = ['project_id', 'type', 'title', 'path', 'mime_type', 'size', 'access_level'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
