<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'professional_name', 'country_id', 'city', 'short_bio', 'biography', 'photo_path', 'website', 'social_links', 'awards', 'association_number', 'status', 'is_public', 'contact_preference', 'phone', 'show_email', 'show_phone', 'submitted_at', 'approved_at'];

    protected $hidden = ['phone', 'status_before_suspension'];

    protected function casts(): array
    {
        return ['social_links' => 'array', 'is_public' => 'boolean', 'show_email' => 'boolean', 'show_phone' => 'boolean', 'submitted_at' => 'datetime', 'approved_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    public function contentTypes(): BelongsToMany
    {
        return $this->belongsToMany(ContentType::class);
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ProfileRole::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProfileDocument::class);
    }

    public function projects(): HasManyThrough
    {
        return $this->hasManyThrough(Project::class, User::class, 'id', 'owner_id', 'user_id', 'id');
    }

    public function scopePublic($q)
    {
        return $q->where('status', 'approved')->where('is_public', true);
    }

    public function scopeProducers($q)
    {
        return $q->whereHas('user', fn ($user) => $user->where('account_type', 'producer'));
    }
}
