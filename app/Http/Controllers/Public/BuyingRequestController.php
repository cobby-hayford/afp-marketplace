<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

 class BuyingRequestController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/buying-requests/index', ['requests' => BuyingRequest::published()->with(['organisation', 'contentTypes', 'genres', 'countries'])->orderBy('deadline')->paginate(18), 'seo' => ['title' => 'Buying Requests | African Film Producers', 'description' => 'See what broadcasters, distributors and buyers are actively seeking.', 'canonical' => url('/buying-requests')]]);
    }

    public function show(BuyingRequest $buyingRequest): Response
    {
        abort_unless($buyingRequest->published()->whereKey($buyingRequest->id)->exists(), 404);
        $buyingRequest->load(['organisation', 'contentTypes', 'genres', 'countries', 'languages']);

        return Inertia::render('public/buying-requests/show', ['respondableProjects' => request()->user() ? Project::managedBy(request()->user())->published()->get(['id', 'title']) : [], 'buyingRequest' => $buyingRequest->only(['id', 'title', 'slug', 'description', 'additional_requirements', 'production_status', 'minimum_production_year', 'runtime_requirements', 'episode_requirements', 'rights_sought', 'territories', 'deadline']) + ['organisation' => $buyingRequest->organisation?->only(['name', 'slug']), 'content_types' => $buyingRequest->contentTypes->pluck('name'), 'genres' => $buyingRequest->genres->pluck('name'), 'countries' => $buyingRequest->countries->pluck('name'), 'languages' => $buyingRequest->languages->pluck('name')], 'seo' => ['title' => $buyingRequest->title.' | Buying Request', 'description' => str($buyingRequest->description)->limit(155)->toString(), 'canonical' => url('/buying-requests/'.$buyingRequest->slug)]]);
    }
}
