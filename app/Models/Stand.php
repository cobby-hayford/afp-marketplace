<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stand extends Model
{
    protected $fillable = ['created_by', 'profile_id', 'organisation_id', 'display_name', 'slug', 'stand_type', 'country_id', 'logo_path', 'cover_path', 'summary', 'description', 'offerings', 'looking_for', 'website', 'social_links', 'is_public', 'featured'];

    protected function casts(): array
    {
        return ['social_links' => 'array', 'is_public' => 'boolean', 'featured' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopePublic($q)
    {
        return $q->where('is_public', true);
    }
}
