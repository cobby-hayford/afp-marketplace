<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveMember;
use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberModerationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Profile::with([
            'user.country',
            'user.adminNotes' => fn ($notes) => $notes->with('actor:id,first_name,last_name')->latest(),
            'country',
            'genres',
            'languages',
            'contentTypes',
            'roles',
            'documents',
        ]);
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        } else {
            $query->whereIn('status', ['pending_review', 'changes_requested', 'submitted']);
        }

        $profiles = $query->latest('submitted_at')->paginate(20)->withQueryString();
        $profiles->through(function (Profile $profile) {
            $profile->setRelation('documents', $profile->documents->map(fn ($document) => [
                'id' => $document->id,
                'type' => $document->type,
                'title' => $document->title,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
                'download_url' => route('profile-documents.download', $document),
            ]));

            return $profile;
        });

        return Inertia::render('admin/memberships', [
            'profiles' => $profiles,
            'filters' => $request->query(),
        ]);
    }

    public function approve(Request $request, Profile $profile, ApproveMember $action): RedirectResponse
    {
        abort_unless(in_array($profile->status, ['submitted', 'pending_review'], true), 422);
        $comments = $request->validate(['comments' => ['nullable', 'string', 'max:3000']])['comments'] ?? null;
        $action->handle($profile, $request->user(), $comments);

        return back()->with('success', 'Member approved.');
    }

    public function transition(Request $request, Profile $profile): RedirectResponse
    {
        abort_unless(in_array($profile->status, ['submitted', 'pending_review'], true), 422);
        $data = $request->validate([
            'status' => ['required', 'in:changes_requested,rejected'],
            'comments' => ['required', 'string', 'max:3000'],
        ]);
        $before = $profile->status;
        $profile->update(['status' => $data['status'], 'is_public' => false]);
        ModerationAction::create([
            'moderatable_type' => $profile->getMorphClass(),
            'moderatable_id' => $profile->id,
            'actor_id' => $request->user()->id,
            'previous_status' => $before,
            'new_status' => $data['status'],
            'comments' => $data['comments'],
        ]);
        AuditService::record('member.'.$data['status'], $profile, ['previous_status' => $before]);
        $profile->user->notify(new MarketplaceNotification(
            $data['status'] === 'rejected' ? 'Membership rejected' : 'Changes requested',
            $data['comments'],
            '/profile',
            $data['status'] === 'rejected' ? 'error' : 'warning'
        ));

        return back()->with('success', 'Member status updated.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'super_administrator' && ! $request->user()->can('manage-admin-roles'), 403);
        abort_if($user->id === $request->user()->id, 422, 'Administrators cannot suspend their own active session.');
        abort_if($user->suspended_at, 422, 'User is already suspended.');

        $user->forceFill(['suspended_at' => now()])->save();
        if ($user->profile && $user->profile->status !== 'suspended') {
            $user->profile->forceFill([
                'status_before_suspension' => $user->profile->status,
                'status' => 'suspended',
                'is_public' => false,
            ])->save();
        }
        AuditService::record('user.suspended', $user);

        return back()->with('success', 'User suspended.');
    }
}
