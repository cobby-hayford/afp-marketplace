<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganisationRequest;
use App\Models\Country;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationController extends Controller
{
    public function index(Request $request): Response
    {
        $organisations = Organisation::where('created_by', $request->user()->id)
            ->orWhereHas('members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->with(['country:id,name', 'members:id,first_name,last_name,email'])
            ->get();
        $organisations->each(fn (Organisation $organisation) => $organisation->makeVisible(['email', 'phone']));

        return Inertia::render('portal/organisations', [
            'organisations' => $organisations,
            'countries' => Country::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(OrganisationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'cover']);
        $data['created_by'] = $request->user()->id;
        $data['slug'] = SlugService::unique(Organisation::class, $data['name']);
        foreach (['logo', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field.'_path'] = $request->file($field)->store('organisations', 'public');
            }
        }
        $organisation = Organisation::create($data);
        $organisation->members()->attach($request->user()->id, ['role' => 'owner']);

        return back()->with('success', 'Organisation created. Submit it for approval when ready.');
    }

    public function update(OrganisationRequest $request, Organisation $organisation): RedirectResponse
    {
        $this->authorizeManagement($request, $organisation);
        $data = $request->safe()->except(['logo', 'cover']);
        foreach (['logo', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                if ($organisation->{$field.'_path'}) {
                    Storage::disk('public')->delete($organisation->{$field.'_path'});
                }
                $data[$field.'_path'] = $request->file($field)->store('organisations', 'public');
            }
        }
        $organisation->update($data);
        if ($organisation->approval_status === 'approved') {
            $organisation->forceFill(['approval_status' => 'pending_review', 'is_public' => false])->save();
        }

        return back()->with('success', 'Organisation updated.');
    }

    public function submit(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->authorizeManagement($request, $organisation);
        abort_unless(in_array($organisation->approval_status, ['draft', 'changes_requested', 'rejected'], true), 422);
        abort_if(blank($organisation->description) || blank($organisation->country_id), 422, 'Complete the organisation description and country before submitting.');
        $organisation->forceFill(['approval_status' => 'pending_review', 'is_public' => false])->save();
        User::whereIn('role', ['administrator', 'super_administrator'])->each(
            fn ($admin) => $admin->notify(new MarketplaceNotification('Organisation submitted', $organisation->name.' requires review.', '/admin/organisations'))
        );

        return back()->with('success', 'Organisation submitted for review.');
    }

    private function authorizeManagement(Request $request, Organisation $organisation): void
    {
        abort_unless($organisation->managedBy($request->user()), 403);
    }
}
