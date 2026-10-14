<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['owner_id', 'organisation_id', 'stand_id', 'title', 'slug', 'alternative_title', 'logline', 'short_synopsis', 'description', 'content_type_id', 'country_id', 'project_stage_id', 'production_year', 'runtime_minutes', 'episode_count', 'episode_runtime_minutes', 'budget', 'currency', 'release_status', 'rights_available', 'territories', 'restricted_territories', 'poster_path', 'cover_path', 'trailer_url', 'teaser_url', 'audio_preview_url', 'screener_url', 'visibility', 'featured', 'publication_status', 'submitted_at', 'published_at'];

    protected function casts(): array
    {
        return ['budget' => 'decimal:2', 'featured' => 'boolean', 'submitted_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class)->withPivot('kind');
    }

    public function originalLanguages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class)->withPivot('kind')->wherePivot('kind', 'original');
    }

    public function subtitleLanguages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class)->withPivot('kind')->wherePivot('kind', 'subtitle');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProjectTag::class, 'project_project_tag');
    }

    public function coProductionCountries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'project_country')->withPivot('relationship');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(ProjectTeamMember::class)->orderBy('sort_order');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function rights(): HasMany
    {
        return $this->hasMany(ProjectRight::class);
    }

    public function festivalRecognitions(): HasMany
    {
        return $this->hasMany(FestivalRecognition::class);
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(AccessGrant::class);
    }

    public function declarations(): HasMany
    {
        return $this->hasMany(CopyrightDeclaration::class);
    }

    public function scopePublished($q)
    {
        return $q->where('publication_status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopePubliclyVisible($q)
    {
        return $q->published()->where('visibility', 'public');
    }

    public function scopeManagedBy($q, User $u)
    {
        return $q->where(fn ($query) => $query->where('owner_id', $u->id)->orWhereHas('organisation', fn ($org) => $org->where('created_by', $u->id)->orWhereHas('members', fn ($member) => $member->where('users.id', $u->id)->whereIn('organisation_members.role', ['owner', 'manager']))));
    }

    public function isManagedBy(User $u): bool
    {
        return $u->id === $this->owner_id || ($this->organisation && $this->organisation->managedBy($u)) || $u->isAdmin();
    }

    public function hasAccess(User $u): bool
    {
        return $this->isManagedBy($u) || $this->accessGrants()->where('user_id', $u->id)->whereNull('revoked_at')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
    }
}
