<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationPost;
use App\Models\ModerationAction;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationModerationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/collaboration', [
            'posts' => CollaborationPost::with(['user', 'type', 'country'])->latest()->paginate(25),
        ]);
    }

    public function update(Request $request, CollaborationPost $collaboration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:published,changes_requested,rejected,suspended'],
            'comments' => ['nullable', 'string', 'max:3000'],
        ]);
        $allowed = $collaboration->publication_status === 'submitted'
            ? ['published', 'changes_requested', 'rejected', 'suspended']
            : ($collaboration->publication_status === 'published' ? ['suspended'] : []);
        abort_unless(in_array($data['status'], $allowed, true), 422);

        if (in_array($data['status'], ['changes_requested', 'rejected'], true)) {
            abort_if(blank($data['comments'] ?? null), 422, 'Moderator comments are required for this decision.');
        }

        $before = $collaboration->publication_status;
        $collaboration->forceFill([
            'publication_status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? now() : null,
        ])->save();
        ModerationAction::create([
            'moderatable_type' => $collaboration->getMorphClass(),
            'moderatable_id' => $collaboration->id,
            'actor_id' => $request->user()->id,
            'previous_status' => $before,
            'new_status' => $data['status'],
            'comments' => $data['comments'] ?? null,
        ]);
        AuditService::record('collaboration.'.$data['status'], $collaboration, ['previous_status' => $before]);
        $collaboration->user->notify(new MarketplaceNotification(
            'Collaboration status updated',
            $collaboration->title.' is now '.$data['status'].'. '.($data['comments'] ?? ''),
            '/my-collaboration'
        ));

        return back()->with('success', 'Collaboration status updated.');
    }
}
