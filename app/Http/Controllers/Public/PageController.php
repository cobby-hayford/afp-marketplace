<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return Inertia::render('public/page', ['page' => $page->only(['title', 'slug', 'content']), 'seo' => ['title' => ($page->seo_title ?: $page->title).' | African Film Producers', 'description' => $page->meta_description, 'canonical' => url('/'.$page->slug)]]);
    }
}
