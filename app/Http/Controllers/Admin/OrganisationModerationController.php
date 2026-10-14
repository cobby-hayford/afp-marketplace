<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use App\Models\Organisation;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationModerationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/organisations', [
            'organisations' => Organisation::with(['creator', 'country'])->latest()->paginate(25),
        ]);
    }

    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        abort_unless($organisation->approval_status === 'pending_review', 422);
        $data = $request->validate([
            'status' => ['required', 'in:approved,changes_requested,rejected'],
            'comments' => ['nullable', 'string', 'max:3000'],
        ]);
        if (in_array($data['status'], ['changes_requested', 'rejected'], true)) {
            abort_if(blank($data['comments'] ?? null), 422, 'Moderator comments are required for this decision.');
        }

        $before = $organisation->approval_status;
        $organisation->forceFill([
            'approval_status' => $data['status'],
            'is_public' => $data['status'] === 'approved',
        ])->save();
        ModerationAction::create([
            'moderatable_type' => $organisation->getMorphClass(),
            'moderatable_id' => $organisation->id,
            'actor_id' => $request->user()->id,
            'previous_status' => $before,
            'new_status' => $data['status'],
            'comments' => $data['comments'] ?? null,
        ]);
        AuditService::record('organisation.'.$data['status'], $organisation, ['previous_status' => $before]);
        $organisation->creator->notify(new MarketplaceNotification(
            'Organisation status updated',
            $organisation->name.' is now '.$data['status'].'. '.($data['comments'] ?? ''),
            '/organisations'
        ));

        return back()->with('success', 'Organisation status updated.');
    }
}
