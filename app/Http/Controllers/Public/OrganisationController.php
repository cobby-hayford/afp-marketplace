<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Services\MarketplacePresenter as P;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationController extends Controller
{
    public function show(Organisation $organisation): Response
    {
        abort_unless($organisation->approval_status === 'approved' && $organisation->is_public, 404);
        $organisation->load(['country', 'members.profile', 'stands']);
        $projects = $organisation->projects()->publiclyVisible()
            ->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions'])
            ->get()->map(fn ($project) => P::projectCard($project));

        $data = [
            'name' => $organisation->name,
            'slug' => $organisation->slug,
            'type' => $organisation->type,
            'country' => $organisation->country?->name,
            'city' => $organisation->city,
            'description' => $organisation->description,
            'logo_url' => P::image($organisation->logo_path),
            'cover_url' => P::image($organisation->cover_path),
            'website' => $organisation->website,
            'social_links' => $organisation->social_links,
            'verified' => $organisation->verification_status === 'verified',
            'projects' => $projects,
            'stands' => $organisation->stands->where('is_public', true)->map(fn ($stand) => P::standCard($stand))->values(),
        ];

        return Inertia::render('public/organisation', [
            'organisation' => $data,
            'seo' => [
                'title' => $organisation->name.' | African Film Producers',
                'description' => str($organisation->description)->limit(155)->toString(),
                'canonical' => url('/organisations/'.$organisation->slug),
                'image' => P::image($organisation->cover_path),
                'jsonLd' => [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        array_filter([
                            '@type' => 'Organization',
                            'name' => $organisation->name,
                            'description' => $organisation->description,
                            'url' => url('/organisations/'.$organisation->slug),
                            'logo' => P::image($organisation->logo_path),
                            'address' => $organisation->country ? [
                                '@type' => 'PostalAddress',
                                'addressLocality' => $organisation->city,
                                'addressCountry' => $organisation->country->name,
                            ] : null,
                        ], fn ($value) => $value !== null),
                        [
                            '@type' => 'BreadcrumbList',
                            'itemListElement' => [
                                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Trade Hall', 'item' => url('/trade-hall')],
                                ['@type' => 'ListItem', 'position' => 3, 'name' => $organisation->name, 'item' => url('/organisations/'.$organisation->slug)],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
