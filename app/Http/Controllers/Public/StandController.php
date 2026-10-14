<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CollaborationPost;
use App\Models\Project;
use App\Models\Stand;
use App\Services\MarketplacePresenter as P;
use Inertia\Inertia;
use Inertia\Response;

class StandController extends Controller
{
    public function index(): Response
    {
        $stands = Stand::public()->with('country')->orderByDesc('featured')->paginate(18);
        $stands->through(fn (Stand $stand) => P::standCard($stand));

        return Inertia::render('public/stands/index', [
            'stands' => $stands,
            'seo' => [
                'title' => 'Digital Stands | African Film Producers',
                'description' => 'Explore producers, companies, broadcasters, distributors and creative organisations in the African Content Trade Hall.',
                'canonical' => url('/stands'),
            ],
        ]);
    }

    public function show(Stand $stand): Response
    {
        abort_unless($stand->is_public, 404);
        $stand->load(['country', 'profile.user', 'organisation.members.profile.user', 'creator.profile']);
        $projects = Project::publiclyVisible()->where('stand_id', $stand->id)
            ->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions'])
            ->get()->map(fn ($project) => P::projectCard($project));
        $collaboration = CollaborationPost::published()
            ->where(fn ($query) => $query->where('user_id', $stand->created_by)
                ->when($stand->organisation_id, fn ($q) => $q->orWhere('organisation_id', $stand->organisation_id)))
            ->with(['type:id,name', 'country:id,name'])
            ->latest('published_at')->limit(8)->get(['id', 'user_id', 'organisation_id', 'collaboration_type_id', 'country_id', 'title', 'slug', 'description', 'closing_date', 'published_at']);

        $team = collect();
        if ($stand->organisation) {
            $team = $stand->organisation->members->filter(fn ($member) => $member->profile?->status === 'approved' && $member->profile?->is_public)
                ->map(fn ($member) => [
                    'name' => $member->profile->professional_name ?: $member->name,
                    'slug' => $member->profile->slug,
                    'role' => str($member->pivot->role)->replace('_', ' ')->title()->toString(),
                ])->values();
        } elseif ($stand->profile?->status === 'approved' && $stand->profile->is_public) {
            $team = collect([[
                'name' => $stand->profile->professional_name ?: $stand->profile->user->name,
                'slug' => $stand->profile->slug,
                'role' => 'Producer',
            ]]);
        }

        return Inertia::render('public/stands/show', [
            'stand' => P::standCard($stand) + [
                'description' => $stand->description,
                'offerings' => $stand->offerings,
                'looking_for' => $stand->looking_for,
                'website' => $stand->website,
                'social_links' => $stand->social_links,
                'projects' => $projects,
                'team' => $team,
                'collaboration' => $collaboration,
            ],
            'seo' => [
                'title' => $stand->display_name.' | Digital Stand',
                'description' => str($stand->summary)->limit(155)->toString(),
                'canonical' => url('/stands/'.$stand->slug),
                'image' => P::image($stand->cover_path),
            ],
        ]);
    }
}
