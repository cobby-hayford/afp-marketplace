<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\CollaborationPost;
use App\Models\NewsPost;
use App\Models\Opportunity;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Stand;
use App\Services\MarketplacePresenter as P;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredProjects = Project::publiclyVisible()->where('featured', true)
            ->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions'])
            ->latest('published_at')->limit(6)->get()->map(fn ($project) => P::projectCard($project));
        $featuredStands = Stand::public()->with('country')->where('featured', true)->limit(6)->get()->map(fn ($stand) => P::standCard($stand));
        $producerQuery = fn () => Profile::public()->producers()
            ->withCount(['projects as published_projects_count' => fn ($query) => $query->publiclyVisible()])
            ->with(['user', 'country', 'contentTypes', 'genres', 'languages', 'roles']);

        return Inertia::render('public/home', [
            'featuredProjects' => $featuredProjects,
            'featuredStands' => $featuredStands,
            'buyingRequests' => BuyingRequest::published()->latest('published_at')->limit(4)->get(['id', 'title', 'slug', 'deadline', 'description']),
            'collaboration' => CollaborationPost::published()->with('type', 'country')->latest('published_at')->limit(4)->get(),
            'opportunities' => Opportunity::published()->with('type', 'country')->latest('published_at')->limit(4)->get(),
            'producers' => $producerQuery()->latest('approved_at')->limit(6)->get()->map(fn ($profile) => P::profileCard($profile)),
            'verifiedMembers' => $producerQuery()->where('verification_status', 'verified')->latest('approved_at')->limit(4)->get()->map(fn ($profile) => P::profileCard($profile)),
            'news' => NewsPost::published()->with('category')->latest('published_at')->limit(4)->get(['id', 'title', 'slug', 'excerpt', 'featured_image_path', 'published_at', 'news_category_id']),
            'stats' => [
                'projects' => Project::publiclyVisible()->count(),
                'producers' => Profile::public()->producers()->count(),
                'stands' => Stand::public()->count(),
                'buying_requests' => BuyingRequest::published()->count(),
            ],
            'seo' => [
                'title' => "Africa's Content. One Marketplace. | African Film Producers",
                'description' => 'Discover African films, music and digital content. Connect with producers, broadcasters, distributors, investors and creative partners.',
                'canonical' => url('/'),
                'jsonLd' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => config('app.name'),
                    'url' => url('/'),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => url('/search').'?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ]);
    }
}
