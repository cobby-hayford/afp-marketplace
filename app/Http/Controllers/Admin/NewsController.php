<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Services\AuditService;
use App\Services\MarketplacePresenter as P;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/news', [
            'posts' => NewsPost::with('category')->latest()->paginate(25),
            'categories' => NewsCategory::orderBy('name')->get(),
        ]);
    }

    public function preview(NewsPost $news): Response
    {
        $news->load('category:id,name,slug', 'author:id,first_name,last_name');

        return Inertia::render('public/news/show', [
            'post' => [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'excerpt' => $news->excerpt,
                'content' => $news->content,
                'featured_image_url' => P::image($news->featured_image_path),
                'published_at' => $news->published_at?->toISOString(),
                'category' => $news->category?->only(['name', 'slug']),
                'author' => ['name' => $news->author->name],
            ],
            'seo' => [
                'title' => '[Preview] '.$news->title.' | African Film Producers',
                'description' => str($news->excerpt)->limit(155)->toString(),
                'canonical' => url('/admin/news/'.$news->id.'/preview'),
                'image' => P::image($news->featured_image_path),
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->data($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = SlugService::unique(NewsPost::class, $data['title']);
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        $data = $this->storeImage($request, $data);
        $post = NewsPost::create($data);
        AuditService::record('news.created', $post);

        return back()->with('success', 'News post created.');
    }

    public function update(Request $request, NewsPost $news): RedirectResponse
    {
        $data = $this->data($request);
        if ($data['status'] === 'published' && ! $news->published_at) {
            $data['published_at'] = now();
        } elseif ($data['status'] !== 'published') {
            $data['published_at'] = null;
        }
        $data = $this->storeImage($request, $data, $news);
        $news->update($data);
        AuditService::record('news.updated', $news);

        return back()->with('success', 'News post updated.');
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'news_category_id' => ['nullable', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:220'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'max:50000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', 'in:draft,published,archived'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=5000'],
        ]);
    }

    private function storeImage(Request $request, array $data, ?NewsPost $post = null): array
    {
        unset($data['featured_image']);
        if (! $request->hasFile('featured_image')) {
            return $data;
        }
        if ($post?->featured_image_path) {
            Storage::disk('public')->delete($post->featured_image_path);
        }
        $data['featured_image_path'] = $request->file('featured_image')->store('news', 'public');

        return $data;
    }
}
