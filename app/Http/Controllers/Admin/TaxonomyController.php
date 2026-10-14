<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationType;
use App\Models\ContentType;
use App\Models\Genre;
use App\Models\NewsCategory;
use App\Models\OpportunityType;
use App\Models\ProjectStage;
use App\Models\ProjectTag;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxonomyController extends Controller
{
    private array $map = ['content-types' => ContentType::class, 'genres' => Genre::class, 'project-stages' => ProjectStage::class, 'project-tags' => ProjectTag::class, 'opportunity-types' => OpportunityType::class, 'collaboration-types' => CollaborationType::class, 'news-categories' => NewsCategory::class];

    public function index(): Response
    {
        $sets = [];
        foreach ($this->map as $key => $class) {
            $sets[$key] = $class::orderBy('name')->get();
        }

return Inertia::render('admin/taxonomies', ['sets' => $sets]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['taxonomy' => 'required|in:'.implode(',', array_keys($this->map)), 'name' => 'required|string|max:160']);
        $class = $this->map[$data['taxonomy']];
        $attrs = ['name' => $data['name']];
        if (in_array($data['taxonomy'], ['content-types', 'genres', 'project-stages', 'project-tags', 'opportunity-types', 'collaboration-types', 'news-categories'], true)) {
            $attrs['slug'] = SlugService::unique($class, $data['name']);
        }$class::create($attrs);

        return back()->with('success', 'Taxonomy value added.');
    }

    public function update(Request $request, string $taxonomy, int $id): RedirectResponse
    {
        abort_unless(isset($this->map[$taxonomy]), 404);
        $data = $request->validate(['name' => 'required|string|max:160', 'active' => 'nullable|boolean']);
        $record = $this->map[$taxonomy]::findOrFail($id);
        $record->update(array_filter($data, fn ($v) => $v !== null));

        return back()->with('success', 'Taxonomy updated.');
    }
}
