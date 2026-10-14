<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FestivalRecognition extends Model
{
    protected $fillable = ['project_id', 'festival_name', 'festival_country_id', 'year', 'selection_type', 'award_category', 'evidence_url', 'evidence_path'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'festival_country_id');
    }
}
