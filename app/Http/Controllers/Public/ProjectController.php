<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Organisation;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\ProjectTag;
use App\Services\MarketplacePresenter as P;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Project::publiclyVisible()->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions']);

        if ($search = trim((string) $request->query('q'))) {
            $like = '%'.mb_strtolower($search).'%';
            $query->where(fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', [$like])->orWhereRaw('LOWER(logline) LIKE ?', [$like]));
        }

        $simpleFilters = ['country_id' => 'country_id', 'content_type_id' => 'content_type_id', 'stage_id' => 'project_stage_id', 'production_year' => 'production_year'];
        foreach ($simpleFilters as $parameter => $column) {
            if ($request->filled($parameter)) {
                $query->where($column, $request->integer($parameter));
            }
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', fn ($q) => $q->whereKey($request->integer('genre_id')));
        }
        if ($request->filled('language_id')) {
            $query->whereHas('languages', fn ($q) => $q->whereKey($request->integer('language_id')));
        }
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', fn ($q) => $q->whereKey($request->integer('tag_id')));
        }
        if ($request->boolean('verified')) {
            $query->whereHas('owner.profile', fn ($q) => $q->where('verification_status', 'verified'));
        }
        if ($request->boolean('festival')) {
            $query->whereHas('festivalRecognitions', fn ($q) => $q->where('verification_status', 'verified'));
        }
        if ($request->query('availability') === 'rights_listed') {
            $query->whereNotNull('rights_available')->where('rights_available', '<>', '');
        } elseif ($request->query('availability') === 'completed') {
            $query->whereHas('stage', fn ($q) => $q->whereIn('name', ['Completed', 'Released', 'Catalogue Title']));
        }

        $sort = (string) $request->query('sort', 'newest');
        if ($sort === 'alphabetical') {
            $query->orderBy('title');
        } elseif ($sort === 'featured') {
            $query->orderByDesc('featured')->latest('published_at');
        } elseif ($sort === 'most_viewed') {
            $query->orderByDesc('view_count');
        } elseif ($sort === 'updated') {
            $query->latest('updated_at');
        } else {
            $query->latest('published_at');
        }

        $projects = $query->paginate(18)->withQueryString();
        $projects->through(fn (Project $project) => P::projectCard($project));

        $countryPage = $request->attributes->get('countryPage');
        $filters = $request->query();
        if ($countryPage) {
            $filters['country_id'] = (string) $countryPage->id;
        }

        return Inertia::render('public/projects/index', [
            'projects' => $projects,
            'filters' => $filters,
            'basePath' => $countryPage ? '/projects/'.$countryPage->slug : '/projects',
            'pageTitle' => $countryPage ? 'Projects from '.$countryPage->name : 'Projects',
            'pageIntro' => $countryPage ? 'Discover approved content projects originating from '.$countryPage->name.' and connect with their producers and rights representatives.' : 'Only moderator-approved project listings appear here. Search and filters remain in the URL so results can be shared.',
            'options' => [
                'countries' => Country::where('is_african', true)->orderBy('name')->get(['id', 'name', 'slug']),
                'contentTypes' => ContentType::where('active', true)->orderBy('name')->get(['id', 'name']),
                'genres' => Genre::orderBy('name')->get(['id', 'name']),
                'stages' => ProjectStage::orderBy('sort_order')->get(['id', 'name']),
                'languages' => Language::orderBy('name')->get(['id', 'name']),
                'tags' => ProjectTag::where('active', true)->orderBy('name')->get(['id', 'name']),
            ],
            'seo' => [
                'title' => $countryPage ? $countryPage->name.' Projects | African Film Producers' : 'Projects | African Film Producers Content Marketplace',
                'description' => $countryPage ? 'Discover approved film, television, music and digital content projects from '.$countryPage->name.'.' : 'Search approved African film, music, television and digital content projects.',
                'canonical' => $countryPage ? url('/projects/'.$countryPage->slug) : url('/projects').($request->getQueryString() ? '?'.$request->getQueryString() : ''),
            ],
        ]);
    }

    public function show(Project $project, Request $request): Response
    {
        abort_unless($project->published()->whereKey($project->id)->exists(), 404);
        if ($project->visibility !== 'public') {
            $viewer = $request->user();
            abort_unless($viewer && ! $viewer->suspended_at && $viewer->hasVerifiedEmail() && $viewer->profile?->status === 'approved', 404);
        }
        $project->increment('view_count');
        $project->load(['owner.profile', 'country', 'contentType', 'stage', 'genres', 'tags', 'originalLanguages', 'subtitleLanguages', 'coProductionCountries', 'teamMembers.profile.user', 'media', 'documents', 'rights', 'festivalRecognitions.country', 'stand', 'accessGrants']);
        $data = P::projectDetail($project, $request->user());
        $related = Project::publiclyVisible()->where('id', '!=', $project->id)->where('content_type_id', $project->content_type_id)->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions'])->limit(4)->get()->map(fn ($p) => P::projectCard($p));
        $creativeWork = array_filter([
            '@type' => in_array($project->contentType?->slug, ['feature-film', 'short-film', 'documentary'], true) ? 'Movie' : 'CreativeWork',
            'name' => $project->title,
            'description' => $project->short_synopsis ?: $project->logline,
            'countryOfOrigin' => $project->country?->name,
            'dateCreated' => $project->production_year ? (string) $project->production_year : null,
            'url' => url('/projects/'.$project->slug),
        ], fn ($value) => $value !== null);
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $creativeWork,
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Projects', 'item' => url('/projects')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $project->title, 'item' => url('/projects/'.$project->slug)],
                    ],
                ],
            ],
        ];

        $requestOrganisations = collect();
        if ($request->user() && $data['can_request_access']) {
            $requestOrganisations = Organisation::where('approval_status', 'approved')
                ->where(fn ($query) => $query->where('created_by', $request->user()->id)
                    ->orWhereHas('members', fn ($member) => $member->where('users.id', $request->user()->id)))
                ->orderBy('name')->get(['id', 'name']);
        }

        return Inertia::render('public/projects/show', [
            'project' => $data,
            'relatedProjects' => $related,
            'requestOrganisations' => $requestOrganisations,
            'seo' => [
                'title' => $project->title.' | African Film Producers',
                'description' => str($project->logline ?: $project->short_synopsis)->limit(155)->toString(),
                'canonical' => url('/projects/'.$project->slug),
                'image' => P::image($project->poster_path),
                'robots' => $project->visibility === 'public' ? 'index,follow' : 'noindex,nofollow',
                'jsonLd' => $project->visibility === 'public' ? $jsonLd : null,
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
        $project = Project::where('slug', $slug)->firstOrFail();

        return $this->show($project, $request);
    }

    public function country(Country $country, Request $request): Response
    {
        $request->merge(['country_id' => $country->id]);

        return $this->index($request);
    }
}
