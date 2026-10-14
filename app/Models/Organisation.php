<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['created_by', 'name', 'slug', 'type', 'country_id', 'city', 'description', 'logo_path', 'cover_path', 'website', 'email', 'phone', 'social_links', 'is_public'];

    protected $hidden = ['email', 'phone'];

    protected function casts(): array
    {
        return ['social_links' => 'array', 'is_public' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_members')->withPivot('role')->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function scopePublic($q)
    {
        return $q->where('approval_status', 'approved')->where('is_public', true);
    }

    public function managedBy(User $u): bool
    {
        return $this->created_by === $u->id || $this->members()->where('users.id', $u->id)->wherePivotIn('role', ['owner', 'manager'])->exists() || $u->isAdmin();
    }
}
