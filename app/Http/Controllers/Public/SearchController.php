<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\NewsPost;
use App\Models\Opportunity;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Stand;
use App\Services\MarketplacePresenter as P;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $r): Response
    {
        $term = trim((string) $r->query('q'));
        $like = '%'.mb_strtolower($term).'%';
        $results = ['projects' => [], 'producers' => [], 'organisations' => [], 'stands' => [], 'buying_requests' => [], 'opportunities' => [], 'news' => []];
        if ($term !== '') {
            $results['projects'] = Project::publiclyVisible()->whereRaw('LOWER(title) LIKE ?', [$like])->with(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions'])->limit(8)->get()->map(fn ($p) => P::projectCard($p));
            $results['producers'] = Profile::public()->producers()->whereRaw("LOWER(COALESCE(professional_name,'')) LIKE ?", [$like])->with(['user', 'country', 'contentTypes', 'genres', 'languages', 'roles'])->limit(8)->get()->map(fn ($p) => P::profileCard($p));
            $results['organisations'] = Organisation::public()->whereRaw('LOWER(name) LIKE ?', [$like])->limit(8)->get(['id', 'name', 'slug', 'type', 'country_id']);
            $results['stands'] = Stand::public()->whereRaw('LOWER(display_name) LIKE ?', [$like])->with('country')->limit(8)->get()->map(fn ($s) => P::standCard($s));
            $results['buying_requests'] = BuyingRequest::published()->whereRaw('LOWER(title) LIKE ?', [$like])->limit(8)->get(['id', 'title', 'slug', 'deadline']);
            $results['opportunities'] = Opportunity::published()->whereRaw('LOWER(title) LIKE ?', [$like])->limit(8)->get(['id', 'title', 'slug', 'deadline']);
            $results['news'] = NewsPost::published()->whereRaw('LOWER(title) LIKE ?', [$like])->limit(8)->get(['id', 'title', 'slug', 'excerpt']);
        }

return Inertia::render('public/search', ['query' => $term, 'results' => $results, 'seo' => ['title' => 'Search | African Film Producers', 'description' => 'Search projects, producers, organisations, stands, buying requests, opportunities and news.', 'canonical' => url('/search').($term ? '?q='.urlencode($term) : '')]]);
    }
}
