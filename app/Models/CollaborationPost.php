<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollaborationPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'organisation_id', 'project_id', 'collaboration_type_id', 'country_id', 'content_type_id', 'genre_id', 'title', 'slug', 'description', 'closing_date', 'publication_status', 'published_at'];

    protected function casts(): array
    {
        return ['closing_date' => 'date', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CollaborationType::class, 'collaboration_type_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function targetCountries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CollaborationResponse::class);
    }

    public function scopePublished($q)
    {
        return $q->where('publication_status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
