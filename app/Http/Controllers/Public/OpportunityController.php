<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Services\MarketplacePresenter as P;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    public function index(): Response
    {
        $opportunities = Opportunity::published()
            ->with(['type:id,name,slug', 'country:id,name,slug'])
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->paginate(18)
            ->through(fn (Opportunity $opportunity) => $this->present($opportunity));

        return Inertia::render('public/opportunities/index', [
            'opportunities' => $opportunities,
            'seo' => [
                'title' => 'Industry Opportunities | African Film Producers',
                'description' => 'Explore film grants, festivals, labs, residencies, markets, training and funding calls.',
                'canonical' => url('/opportunities'),
            ],
        ]);
    }

    public function show(Opportunity $opportunity): Response
    {
        abort_unless($opportunity->publication_status === 'published' && $opportunity->published_at?->lte(now()), 404);
        $opportunity->load(['type:id,name,slug', 'country:id,name,slug', 'eligibleCountries:id,name,slug']);
        $data = $this->present($opportunity, true);

        return Inertia::render('public/opportunities/show', [
            'opportunity' => $data,
            'seo' => [
                'title' => $opportunity->title.' | Opportunity',
                'description' => str($opportunity->description)->limit(155)->toString(),
                'canonical' => url('/opportunities/'.$opportunity->slug),
                'image' => P::image($opportunity->featured_image_path),
            ],
        ]);
    }

    private function present(Opportunity $opportunity, bool $full = false): array
    {
        return [
            'id' => $opportunity->id,
            'title' => $opportunity->title,
            'slug' => $opportunity->slug,
            'organisation' => $opportunity->organisation,
            'description' => $opportunity->description,
            'eligibility' => $full ? $opportunity->eligibility : null,
            'opening_date' => $opportunity->opening_date?->toDateString(),
            'deadline' => $opportunity->deadline?->toDateString(),
            'external_application_url' => $full ? $opportunity->external_application_url : null,
            'featured_image_url' => P::image($opportunity->featured_image_path),
            'funding_amount' => $opportunity->funding_amount,
            'currency' => $opportunity->currency,
            'featured' => (bool) $opportunity->featured,
            'type' => $opportunity->type?->only(['id', 'name', 'slug']),
            'country' => $opportunity->country?->only(['id', 'name', 'slug']),
            'eligible_countries' => $full ? $opportunity->eligibleCountries->map->only(['id', 'name', 'slug'])->values() : [],
        ];
    }
}
