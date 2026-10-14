<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Profile;
use App\Models\ProfileRole;
use App\Services\MarketplacePresenter as P;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProducerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Profile::public()->producers()
            ->withCount(['projects as published_projects_count' => fn ($q) => $q->publiclyVisible()])
            ->with(['user', 'country', 'contentTypes', 'genres', 'languages', 'roles']);

        if ($search = trim((string) $request->query('q'))) {
            $like = '%'.mb_strtolower($search).'%';
            $query->where(fn ($q) => $q
                ->whereRaw("LOWER(COALESCE(professional_name,'')) LIKE ?", [$like])
                ->orWhereHas('user', fn ($user) => $user->whereRaw("LOWER(first_name || ' ' || last_name) LIKE ?", [$like]))
            );
        }
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }
        if ($request->filled('genre_id')) {
            $query->whereHas('genres', fn ($q) => $q->whereKey($request->integer('genre_id')));
        }
        if ($request->filled('content_type_id')) {
            $query->whereHas('contentTypes', fn ($q) => $q->whereKey($request->integer('content_type_id')));
        }
        if ($request->filled('language_id')) {
            $query->whereHas('languages', fn ($q) => $q->whereKey($request->integer('language_id')));
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('role', $request->query('role')));
        }
        if ($request->boolean('verified')) {
            $query->where('verification_status', 'verified');
        }

        $profiles = $query->orderBy('professional_name')->paginate(18)->withQueryString();
        $profiles->through(fn ($profile) => P::profileCard($profile));

        $countryPage = $request->attributes->get('countryPage');
        $filters = $request->query();
        if ($countryPage) {
            $filters['country_id'] = (string) $countryPage->id;
        }

        return Inertia::render('public/producers/index', [
            'producers' => $profiles,
            'filters' => $filters,
            'basePath' => $countryPage ? '/producers/'.$countryPage->slug : '/producers',
            'pageTitle' => $countryPage ? 'Producers in '.$countryPage->name : 'Producers Across Africa',
            'pageIntro' => $countryPage ? 'Discover approved producers and creative professionals based in '.$countryPage->name.'.' : 'Approved professional profiles grouped through database-driven countries and industry specialties.',
            'options' => [
                'countries' => Country::where('is_african', true)->orderBy('name')->get(['id', 'name']),
                'contentTypes' => ContentType::orderBy('name')->get(['id', 'name']),
                'genres' => Genre::orderBy('name')->get(['id', 'name']),
                'languages' => Language::orderBy('name')->get(['id', 'name']),
                'roles' => ProfileRole::query()->select('role')->distinct()->orderBy('role')->pluck('role'),
            ],
            'seo' => [
                'title' => $countryPage ? $countryPage->name.' Producers | African Film Producers' : 'African Producers Directory | African Film Producers',
                'description' => $countryPage ? 'Discover approved film and content producers in '.$countryPage->name.'.' : 'Discover approved producers and creative professionals across Africa.',
                'canonical' => $countryPage ? url('/producers/'.$countryPage->slug) : url('/producers').($request->getQueryString() ? '?'.$request->getQueryString() : ''),
            ],
        ]);
    }

    public function show(Profile $profile): Response
    {
        abort_unless($profile->status === 'approved' && $profile->is_public && $profile->user?->account_type === 'producer', 404);
        $profile->load(['user', 'country', 'contentTypes', 'genres', 'languages', 'roles']);
        $data = P::profileCard($profile) + [
            'biography' => $profile->biography,
            'website' => $profile->website,
            'social_links' => $profile->social_links,
            'association_number' => $profile->association_number,
            'awards' => $profile->awards,
            'email' => $profile->show_email ? $profile->user->email : null,
            'phone' => $profile->show_phone ? $profile->phone : null,
            'projects' => $profile->user->projects()->publiclyVisible()->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions'])->get()->map(fn ($project) => P::projectCard($project)),
        ];

        return Inertia::render('public/producers/show', [
            'profile' => $data,
            'seo' => [
                'title' => $data['name'].' | African Film Producers',
                'description' => str($profile->short_bio)->limit(155)->toString(),
                'canonical' => url('/producers/'.$profile->slug),
                'image' => P::image($profile->photo_path),
                'jsonLd' => [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        array_filter([
                            '@type' => 'Person',
                            'name' => $data['name'],
                            'description' => $profile->short_bio,
                            'url' => url('/producers/'.$profile->slug),
                            'nationality' => $profile->country?->name,
                        ]),
                        [
                            '@type' => 'BreadcrumbList',
                            'itemListElement' => [
                                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Producers', 'item' => url('/producers')],
                                ['@type' => 'ListItem', 'position' => 3, 'name' => $data['name'], 'item' => url('/producers/'.$profile->slug)],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function resolve(string $slug, Request $request): Response
    {
        $country = Country::where('slug', $slug)->where('is_african', true)->first();
        if ($country) {
            $request->merge(['country_id' => $country->id]);
            $request->attributes->set('countryPage', $country);

            return $this->index($request);
        }

        return $this->show(Profile::where('slug', $slug)->firstOrFail());
    }
}
