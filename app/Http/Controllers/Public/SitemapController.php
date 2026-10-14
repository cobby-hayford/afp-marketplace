<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\NewsPost;
use App\Models\Opportunity;
use App\Models\Organisation;
use App\Models\Page;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Stand;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            url('/'),
            url('/trade-hall'),
            url('/projects'),
            url('/producers'),
            url('/stands'),
            url('/buying-requests'),
            url('/collaboration'),
            url('/opportunities'),
            url('/news'),
            url('/about'),
            url('/contact'),
        ]);

        $africanCountries = Country::where('is_african', true)->pluck('slug');
        $urls = $urls
            ->merge($africanCountries->map(fn ($slug) => url('/projects/'.$slug)))
            ->merge($africanCountries->map(fn ($slug) => url('/producers/'.$slug)))
            ->merge(Project::publiclyVisible()->pluck('slug')->map(fn ($slug) => url('/projects/'.$slug)))
            ->merge(Profile::public()->producers()->pluck('slug')->map(fn ($slug) => url('/producers/'.$slug)))
            ->merge(Stand::public()->pluck('slug')->map(fn ($slug) => url('/stands/'.$slug)))
            ->merge(Organisation::public()->pluck('slug')->map(fn ($slug) => url('/organisations/'.$slug)))
            ->merge(Opportunity::published()->pluck('slug')->map(fn ($slug) => url('/opportunities/'.$slug)))
            ->merge(NewsPost::published()->pluck('slug')->map(fn ($slug) => url('/news/'.$slug)))
            ->merge(Page::where('status', 'published')->pluck('slug')->map(fn ($slug) => url('/'.$slug)));

        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$urls->unique()->map(fn ($url) => '<url><loc>'.htmlspecialchars($url, ENT_XML1).'</loc></url>')->implode('')
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
