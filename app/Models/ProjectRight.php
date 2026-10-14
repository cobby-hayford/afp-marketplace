<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRight extends Model
{
    protected $fillable = ['project_id', 'right_type', 'territory', 'availability'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
