<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Services\MarketplacePresenter as P;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(): Response
    {
        $posts = NewsPost::published()
            ->with('category:id,name,slug')
            ->with('author:id,first_name,last_name')
            ->latest('published_at')
            ->paginate(15)
            ->through(fn (NewsPost $post) => $this->present($post));

        return Inertia::render('public/news/index', [
            'posts' => $posts,
            'seo' => [
                'title' => 'News | African Film Producers',
                'description' => 'Industry news, association updates, festivals, funding, distribution and production stories.',
                'canonical' => url('/news'),
            ],
        ]);
    }

    public function show(NewsPost $news): Response
    {
        abort_unless($news->status === 'published' && $news->published_at?->lte(now()), 404);
        $news->load('category:id,name,slug', 'author:id,first_name,last_name');

        return Inertia::render('public/news/show', [
            'post' => $this->present($news, true),
            'seo' => [
                'title' => ($news->seo_title ?: $news->title).' | African Film Producers',
                'description' => $news->meta_description ?: str($news->excerpt)->limit(155)->toString(),
                'canonical' => url('/news/'.$news->slug),
                'image' => P::image($news->featured_image_path),
                'type' => 'article',
                'jsonLd' => [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        array_filter([
                            '@type' => 'Article',
                            'headline' => $news->title,
                            'datePublished' => $news->published_at?->toIso8601String(),
                            'author' => ['@type' => 'Person', 'name' => $news->author->name],
                            'image' => P::image($news->featured_image_path),
                            'url' => url('/news/'.$news->slug),
                        ]),
                        [
                            '@type' => 'BreadcrumbList',
                            'itemListElement' => [
                                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                                ['@type' => 'ListItem', 'position' => 2, 'name' => 'News', 'item' => url('/news')],
                                ['@type' => 'ListItem', 'position' => 3, 'name' => $news->title, 'item' => url('/news/'.$news->slug)],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function present(NewsPost $post, bool $full = false): array
    {
        return array_filter([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $full ? $post->content : null,
            'featured_image_url' => P::image($post->featured_image_path),
            'published_at' => $post->published_at?->toISOString(),
            'category' => $post->category ? ['name' => $post->category->name, 'slug' => $post->category->slug] : null,
            'author' => ['name' => $post->author->name],
        ], fn ($value) => $value !== null);
    }
}
