<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class MarketplacePresenter
{
    public static function image(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public static function projectCard(Project $p): array
    {
        return ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug, 'country' => $p->country?->name, 'country_slug' => $p->country?->slug, 'content_type' => $p->contentType?->name, 'stage' => $p->stage?->name, 'genres' => $p->genres->pluck('name')->values(), 'tags' => $p->tags->pluck('name')->values(), 'budget' => $p->budget, 'currency' => $p->currency, 'logline' => $p->logline, 'poster_url' => self::image($p->poster_path), 'featured' => (bool) $p->featured, 'view_count' => $p->view_count, 'listing_approved' => $p->publication_status === 'published', 'festival_verified' => $p->festivalRecognitions->contains('verification_status', 'verified')];
    }

    public static function projectDetail(Project $p, ?User $viewer): array
    {
        $access = $viewer && $p->hasAccess($viewer);
        $owner = $viewer && $p->isManagedBy($viewer);
        $docs = $p->documents->filter(function ($d) use ($viewer, $access, $owner) {
            return match ($d->access_level) {
                'public' => true,'authenticated' => (bool) $viewer,'request_required' => $access,'private' => $owner,default => false
            };
        })->map(fn ($d) => ['id' => $d->id, 'title' => $d->title, 'type' => $d->type, 'access_level' => $d->access_level, 'download_url' => route('project-documents.download', $d)]);

        return [...self::projectCard($p), 'alternative_title' => $p->alternative_title, 'short_synopsis' => $p->short_synopsis, 'description' => $p->description, 'production_year' => $p->production_year, 'runtime_minutes' => $p->runtime_minutes, 'episode_count' => $p->episode_count, 'episode_runtime_minutes' => $p->episode_runtime_minutes, 'rights_available' => $p->rights_available, 'territories' => $p->territories, 'commercial_rights' => $p->relationLoaded('rights') ? $p->rights->map(fn ($right) => ['right_type' => $right->right_type, 'territory' => $right->territory, 'availability' => $right->availability])->values() : [], 'trailer_url' => $p->trailer_url, 'teaser_url' => $p->teaser_url, 'audio_preview_url' => $p->audio_preview_url, 'cover_url' => self::image($p->cover_path), 'languages' => $p->originalLanguages->pluck('name')->values(), 'subtitle_languages' => $p->subtitleLanguages->pluck('name')->values(), 'co_production_countries' => $p->coProductionCountries->pluck('name')->values(), 'team' => $p->teamMembers->map(fn ($m) => ['role' => $m->role, 'name' => $m->profile?->professional_name ?: $m->profile?->user?->name ?: $m->external_name, 'profile_slug' => $m->profile?->slug]), 'media' => $p->media->map(fn ($m) => ['type' => $m->type, 'caption' => $m->caption, 'url' => $m->path ? self::image($m->path) : $m->url]), 'documents' => $docs->values(), 'recognition' => $p->festivalRecognitions->map(fn ($f) => ['festival_name' => $f->festival_name, 'country' => $f->country?->name, 'year' => $f->year, 'selection_type' => $f->selection_type, 'award_category' => $f->award_category, 'verification_status' => $f->verification_status]), 'owner' => ['name' => $p->owner->profile?->professional_name ?: $p->owner->name, 'profile_slug' => $p->owner->profile?->slug, 'verified' => $p->owner->profile?->verification_status === 'verified'], 'stand' => $p->stand ? ['name' => $p->stand->display_name, 'slug' => $p->stand->slug] : null, 'restricted' => $access || $owner ? ['screener_url' => $p->screener_url] : null, 'has_access' => (bool) ($access || $owner), 'can_request_access' => (bool) ($viewer && ! $viewer->suspended_at && $viewer->hasVerifiedEmail() && $viewer->profile?->status === 'approved' && ! $owner && ! $access)];
    }

    public static function profileCard(Profile $p): array
    {
        return ['id' => $p->id, 'name' => $p->professional_name ?: $p->user->name, 'slug' => $p->slug, 'country' => $p->country?->name, 'country_slug' => $p->country?->slug, 'photo_url' => self::image($p->photo_path), 'short_bio' => $p->short_bio, 'roles' => $p->relationLoaded('roles') ? $p->roles->pluck('role')->values() : $p->roles()->pluck('role')->values(), 'specialties' => $p->contentTypes->pluck('name')->values(), 'genres' => $p->genres->pluck('name')->values(), 'languages' => $p->languages->pluck('name')->values(), 'verified' => $p->verification_status === 'verified', 'project_count' => (int) ($p->published_projects_count ?? $p->projects()->publiclyVisible()->count())];
    }

    public static function standCard(Stand $s): array
    {
        return ['id' => $s->id, 'display_name' => $s->display_name, 'slug' => $s->slug, 'stand_type' => $s->stand_type, 'country' => $s->country?->name, 'summary' => $s->summary, 'logo_url' => self::image($s->logo_path), 'cover_url' => self::image($s->cover_path), 'featured' => (bool) $s->featured, 'verified' => $s->verification_status === 'verified'];
    }
}
