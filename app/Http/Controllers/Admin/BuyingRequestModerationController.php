<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\ModerationAction;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyingRequestModerationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/buying-requests', [
            'requests' => BuyingRequest::with(['user', 'organisation'])->latest()->paginate(25),
        ]);
    }

    public function update(Request $request, BuyingRequest $buyingRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:published,changes_requested,rejected,suspended'],
            'comments' => ['nullable', 'string', 'max:3000'],
        ]);
        $allowed = $buyingRequest->publication_status === 'submitted'
            ? ['published', 'changes_requested', 'rejected', 'suspended']
            : ($buyingRequest->publication_status === 'published' ? ['suspended'] : []);
        abort_unless(in_array($data['status'], $allowed, true), 422);

        if (in_array($data['status'], ['changes_requested', 'rejected'], true)) {
            abort_if(blank($data['comments'] ?? null), 422, 'Moderator comments are required for this decision.');
        }

        $before = $buyingRequest->publication_status;
        $buyingRequest->forceFill([
            'publication_status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? now() : null,
        ])->save();
        ModerationAction::create([
            'moderatable_type' => $buyingRequest->getMorphClass(),
            'moderatable_id' => $buyingRequest->id,
            'actor_id' => $request->user()->id,
            'previous_status' => $before,
            'new_status' => $data['status'],
            'comments' => $data['comments'] ?? null,
        ]);
        AuditService::record('buying_request.'.$data['status'], $buyingRequest, ['previous_status' => $before]);
        $buyingRequest->user->notify(new MarketplaceNotification(
            'Buying request status updated',
            $buyingRequest->title.' is now '.$data['status'].'. '.($data['comments'] ?? ''),
            '/my-buying-requests'
        ));

        return back()->with('success', 'Buying request status updated.');
    }
}
