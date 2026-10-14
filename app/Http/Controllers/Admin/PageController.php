<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/pages', ['pages' => Page::orderBy('title')->get()]);
    }

    public function update(Request $r, Page $page): RedirectResponse
    {
        $d = $r->validate(['title' => 'required|string|max:200', 'content' => 'required|string|max:50000', 'seo_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:320', 'status' => 'required|in:draft,published']);
        $page->update($d);
        AuditService::record('page.updated', $page);

        return back()->with('success', 'Page updated.');
    }
}
