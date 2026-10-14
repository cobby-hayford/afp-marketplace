<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CollaborationPost;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/collaboration/index', [
            'posts' => CollaborationPost::published()->with(['type:id,name', 'country:id,name'])->orderBy('closing_date')->paginate(18),
            'seo' => [
                'title' => 'Collaboration | African Film Producers',
                'description' => 'Find co-producers, directors, composers, investors, distributors and production partners.',
                'canonical' => url('/collaboration'),
            ],
        ]);
    }

    public function show(CollaborationPost $collaboration): Response
    {
        abort_unless($collaboration->published()->whereKey($collaboration->id)->exists(), 404);
        $collaboration->load(['type:id,name', 'country:id,name', 'project:id,title,slug,publication_status', 'targetCountries:id,name']);

        return Inertia::render('public/collaboration/show', [
            'post' => [
                'id' => $collaboration->id,
                'title' => $collaboration->title,
                'slug' => $collaboration->slug,
                'description' => $collaboration->description,
                'closing_date' => $collaboration->closing_date,
                'type' => $collaboration->type?->only(['id', 'name']),
                'country' => $collaboration->country?->only(['id', 'name']),
                'project' => $collaboration->project && $collaboration->project->publiclyVisible()->whereKey($collaboration->project->id)->exists()
                    ? $collaboration->project->only(['id', 'title', 'slug']) : null,
                'target_countries' => $collaboration->targetCountries->map->only(['id', 'name'])->values(),
            ],
            'seo' => [
                'title' => $collaboration->title.' | Collaboration',
                'description' => str($collaboration->description)->limit(155)->toString(),
                'canonical' => url('/collaboration/'.$collaboration->slug),
            ],
        ]);
    }
}
