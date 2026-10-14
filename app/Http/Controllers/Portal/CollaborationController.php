<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\CollaborationRequest;
use App\Models\CollaborationPost;
use App\Models\CollaborationType;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('portal/collaboration', [
            'posts' => CollaborationPost::where('user_id', $request->user()->id)
                ->with(['type:id,name', 'country:id,name', 'responses.user:id,first_name,last_name'])
                ->latest()->paginate(20),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('portal/collaboration-form', ['post' => null, 'options' => $this->options($request)]);
    }

    public function edit(Request $request, CollaborationPost $collaboration): Response
    {
        abort_unless($collaboration->user_id === $request->user()->id, 403);
        abort_unless(in_array($collaboration->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        $collaboration->load('targetCountries:id,name');

        return Inertia::render('portal/collaboration-form', ['post' => $collaboration, 'options' => $this->options($request)]);
    }

    public function store(CollaborationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['target_countries']);
        if ($data['project_id'] ?? null) {
            abort_unless($request->user()->projects()->whereKey($data['project_id'])->exists(), 403);
        }
        if ($data['organisation_id'] ?? null) {
            $organisation = Organisation::findOrFail($data['organisation_id']);
            abort_unless($organisation->managedBy($request->user()), 403);
        }
        $data['user_id'] = $request->user()->id;
        $data['slug'] = SlugService::unique(CollaborationPost::class, $data['title']);
        $post = CollaborationPost::create($data);
        $post->targetCountries()->sync($request->input('target_countries', []));

        return redirect()->route('my-collaboration.index')->with('success', 'Collaboration post saved as draft.');
    }

    public function update(CollaborationRequest $request, CollaborationPost $collaboration): RedirectResponse
    {
        abort_unless($collaboration->user_id === $request->user()->id, 403);
        abort_unless(in_array($collaboration->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        $data = $request->safe()->except(['target_countries']);
        if ($data['project_id'] ?? null) {
            abort_unless($request->user()->projects()->whereKey($data['project_id'])->exists(), 403);
        }
        if ($data['organisation_id'] ?? null) {
            abort_unless(Organisation::findOrFail($data['organisation_id'])->managedBy($request->user()), 403);
        }
        $collaboration->update($data);
        $collaboration->targetCountries()->sync($request->input('target_countries', []));

        return redirect()->route('my-collaboration.index')->with('success', 'Collaboration post updated.');
    }

    public function submit(Request $request, CollaborationPost $collaboration): RedirectResponse
    {
        abort_unless($collaboration->user_id === $request->user()->id, 403);
        abort_unless(in_array($collaboration->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        $collaboration->forceFill(['publication_status' => 'submitted'])->save();
        User::whereIn('role', ['administrator', 'super_administrator'])->each(
            fn ($admin) => $admin->notify(new MarketplaceNotification('Collaboration submitted', $collaboration->title.' requires review.', '/admin/collaboration'))
        );

        return back()->with('success', 'Collaboration submitted for moderation.');
    }

    private function options(Request $request): array
    {
        return [
            'types' => CollaborationType::orderBy('name')->get(['id', 'name']),
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'contentTypes' => ContentType::orderBy('name')->get(['id', 'name']),
            'genres' => Genre::orderBy('name')->get(['id', 'name']),
            'projects' => $request->user()->projects()->where('publication_status', 'published')->get(['id', 'title']),
            'organisations' => Organisation::where(fn ($q) => $q->where('created_by', $request->user()->id)->orWhereHas('members', fn ($x) => $x->where('users.id', $request->user()->id)))->get(['id', 'name']),
        ];
    }
}
