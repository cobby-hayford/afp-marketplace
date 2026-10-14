<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Opportunity;
use App\Models\OpportunityType;
use App\Services\AuditService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/opportunities', [
            'opportunities' => Opportunity::with(['type', 'country', 'eligibleCountries:id,name'])->latest()->paginate(25),
            'types' => OpportunityType::orderBy('name')->get(),
            'countries' => Country::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        $data['slug'] = SlugService::unique(Opportunity::class, $data['title']);
        $data['published_at'] = $data['publication_status'] === 'published' ? now() : null;
        $data = $this->storeImage($request, $data);
        $opportunity = Opportunity::create($data);
        $opportunity->eligibleCountries()->sync($request->input('eligible_countries', []));
        AuditService::record('opportunity.created', $opportunity);

        return back()->with('success', 'Opportunity created.');
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $data = $this->validateData($request);
        if ($data['publication_status'] === 'published' && ! $opportunity->published_at) {
            $data['published_at'] = now();
        } elseif ($data['publication_status'] !== 'published') {
            $data['published_at'] = null;
        }
        $data = $this->storeImage($request, $data, $opportunity);
        $opportunity->update($data);
        $opportunity->eligibleCountries()->sync($request->input('eligible_countries', []));
        AuditService::record('opportunity.updated', $opportunity);

        return back()->with('success', 'Opportunity updated.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'opportunity_type_id' => ['required', 'exists:opportunity_types,id'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'organisation' => ['nullable', 'string', 'max:200'],
            'title' => ['required', 'string', 'max:220'],
            'description' => ['required', 'string', 'max:10000'],
            'eligibility' => ['nullable', 'string', 'max:6000'],
            'opening_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'external_application_url' => ['nullable', 'url', 'max:2048'],
            'funding_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'featured' => ['boolean'],
            'publication_status' => ['required', 'in:draft,published,archived'],
            'eligible_countries' => ['array'],
            'eligible_countries.*' => ['exists:countries,id'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=5000'],
        ]);
    }

    private function storeImage(Request $request, array $data, ?Opportunity $opportunity = null): array
    {
        unset($data['featured_image']);
        if (! $request->hasFile('featured_image')) {
            return $data;
        }
        if ($opportunity?->featured_image_path) {
            Storage::disk('public')->delete($opportunity->featured_image_path);
        }
        $data['featured_image_path'] = $request->file('featured_image')->store('opportunities', 'public');

        return $data;
    }
}
