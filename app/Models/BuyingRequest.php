<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyingRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'organisation_id', 'title', 'slug', 'description', 'additional_requirements', 'production_status', 'minimum_production_year', 'runtime_requirements', 'episode_requirements', 'rights_sought', 'territories', 'deadline', 'publication_status', 'published_at'];

    protected function casts(): array
    {
        return ['deadline' => 'date', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function contentTypes(): BelongsToMany
    {
        return $this->belongsToMany(ContentType::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(BuyingRequestResponse::class);
    }

    public function scopePublished($q)
    {
        return $q->where('publication_status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
