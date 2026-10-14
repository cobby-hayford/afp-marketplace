<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyingRequestForm;
use App\Models\BuyingRequest;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyingRequestController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('portal/buying-requests', [
            'requests' => BuyingRequest::where('user_id', $request->user()->id)
                ->with([
                    'organisation:id,name,slug',
                    'responses.user:id,first_name,last_name',
                    'responses.project:id,owner_id,title,slug,publication_status,content_type_id,country_id,project_stage_id',
                    'responses.project.contentType:id,name',
                    'responses.project.country:id,name',
                    'responses.project.stage:id,name',
                ])->latest()->paginate(20),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('create', BuyingRequest::class), 403);

        return Inertia::render('portal/buying-request-form', ['buyingRequest' => null, 'options' => $this->options($request)]);
    }

    public function edit(Request $request, BuyingRequest $buyingRequest): Response
    {
        abort_unless($request->user()->can('update', $buyingRequest), 403);
        abort_unless(in_array($buyingRequest->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        $buyingRequest->load(['contentTypes:id,name', 'genres:id,name', 'countries:id,name', 'languages:id,name']);

        return Inertia::render('portal/buying-request-form', ['buyingRequest' => $buyingRequest, 'options' => $this->options($request)]);
    }

    public function store(BuyingRequestForm $request): RedirectResponse
    {
        abort_unless($request->user()->can('create', BuyingRequest::class), 403);
        $data = $request->safe()->except(['content_types', 'genres', 'countries', 'languages']);
        if ($data['organisation_id'] ?? null) {
            $organisation = Organisation::findOrFail($data['organisation_id']);
            abort_unless($organisation->managedBy($request->user()), 403);
        }
        $data['user_id'] = $request->user()->id;
        $data['slug'] = SlugService::unique(BuyingRequest::class, $data['title']);
        $buyingRequest = BuyingRequest::create($data);
        $this->sync($request, $buyingRequest);

        return redirect()->route('my-buying-requests.index')->with('success', 'Buying request saved as draft.');
    }

    public function update(BuyingRequestForm $request, BuyingRequest $buyingRequest): RedirectResponse
    {
        abort_unless($request->user()->can('update', $buyingRequest), 403);
        abort_unless(in_array($buyingRequest->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        $data = $request->safe()->except(['content_types', 'genres', 'countries', 'languages']);
        if ($data['organisation_id'] ?? null) {
            $organisation = Organisation::findOrFail($data['organisation_id']);
            abort_unless($organisation->managedBy($request->user()), 403);
        }
        $buyingRequest->update($data);
        $this->sync($request, $buyingRequest);

        return redirect()->route('my-buying-requests.index')->with('success', 'Buying request updated.');
    }

    public function submit(Request $request, BuyingRequest $buyingRequest): RedirectResponse
    {
        abort_unless($request->user()->can('update', $buyingRequest), 403);
        abort_unless(in_array($buyingRequest->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        $buyingRequest->forceFill(['publication_status' => 'submitted'])->save();
        User::whereIn('role', ['administrator', 'super_administrator'])->each(
            fn ($admin) => $admin->notify(new MarketplaceNotification('Buying request submitted', $buyingRequest->title.' requires review.', '/admin/buying-requests'))
        );

        return back()->with('success', 'Buying request submitted for moderation.');
    }

    private function sync(BuyingRequestForm $request, BuyingRequest $buyingRequest): void
    {
        $buyingRequest->contentTypes()->sync($request->input('content_types', []));
        $buyingRequest->genres()->sync($request->input('genres', []));
        $buyingRequest->countries()->sync($request->input('countries', []));
        $buyingRequest->languages()->sync($request->input('languages', []));
    }

    private function options(Request $request): array
    {
        return [
            'contentTypes' => ContentType::orderBy('name')->get(['id', 'name']),
            'genres' => Genre::orderBy('name')->get(['id', 'name']),
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'languages' => Language::orderBy('name')->get(['id', 'name']),
            'organisations' => Organisation::where(fn ($q) => $q->where('created_by', $request->user()->id)->orWhereHas('members', fn ($x) => $x->where('users.id', $request->user()->id)))->get(['id', 'name']),
        ];
    }
}
