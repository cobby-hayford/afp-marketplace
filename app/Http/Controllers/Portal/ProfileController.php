<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $profile = $request->user()->profile?->load(['country', 'genres', 'languages', 'contentTypes', 'roles', 'documents']);
        if ($profile) {
            $profile->makeVisible('phone');
            $profile->setRelation('documents', $profile->documents->map(fn ($document) => [
                'id' => $document->id,
                'type' => $document->type,
                'title' => $document->title,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
                'download_url' => route('profile-documents.download', $document),
            ]));
        }

        return Inertia::render('portal/profile', [
            'profile' => $profile,
            'options' => [
                'countries' => Country::orderBy('name')->get(['id', 'name']),
                'genres' => Genre::orderBy('name')->get(['id', 'name']),
                'languages' => Language::orderBy('name')->get(['id', 'name']),
                'contentTypes' => ContentType::orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->profile ?: new Profile(['user_id' => $user->id]);
        $data = $request->safe()->except(['photo', 'roles', 'genres', 'languages', 'content_types']);
        $data['slug'] = $profile->slug ?: SlugService::unique(Profile::class, $data['professional_name'] ?: $user->name);
        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('profiles', 'public');
        }
        $profile->fill($data)->save();
        $profile->genres()->sync($request->input('genres', []));
        $profile->languages()->sync($request->input('languages', []));
        $profile->contentTypes()->sync($request->input('content_types', []));
        $profile->roles()->delete();
        foreach ($request->input('roles', []) as $role) {
            $profile->roles()->create(['role' => $role]);
        }

        return back()->with('success', 'Profile saved.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $profile = $request->user()->profile;
        abort_unless($profile && $request->user()->hasVerifiedEmail(), 403);
        abort_unless(in_array($profile->status, ['draft', 'changes_requested', 'rejected'], true), 422);
        abort_unless($profile->biography && $profile->short_bio && $profile->country_id, 422, 'Complete the required professional profile fields first.');
        $profile->update(['status' => 'pending_review', 'submitted_at' => now()]);
        User::whereIn('role', ['administrator', 'super_administrator'])->each(fn ($admin) => $admin->notify(new MarketplaceNotification(
            'Member application submitted',
            $request->user()->name.' submitted a profile for approval.',
            '/admin/memberships'
        )));

        return back()->with('success', 'Profile submitted for review.');
    }
}
