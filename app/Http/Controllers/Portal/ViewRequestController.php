<?php

namespace App\Http\Controllers\Portal;

use App\Actions\ApproveViewRequest;
use App\Actions\RequestProjectAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\ViewRequestForm;
use App\Models\AccessGrant;
use App\Models\Project;
use App\Models\ViewRequest;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ViewRequestController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('portal/view-requests', [
            'incoming' => ViewRequest::with(['requester.profile', 'project'])
                ->whereIn('project_id', Project::managedBy($request->user())->select('projects.id'))
                ->latest()->paginate(15, '*', 'incoming'),
            'outgoing' => ViewRequest::with('project')->where('requester_id', $request->user()->id)->latest()->limit(20)->get(),
        ]);
    }

    public function store(ViewRequestForm $request, Project $project, RequestProjectAccess $action): RedirectResponse
    {
        abort_unless($project->published()->whereKey($project->id)->exists(), 404);
        $action->handle($project, $request->user(), $request->validated());

        return back()->with('success', 'Viewing request sent to the project owner.');
    }

    public function approve(Request $request, ViewRequest $viewRequest, ApproveViewRequest $action): RedirectResponse
    {
        abort_unless($request->user()->can('decide', $viewRequest), 403);
        $note = $request->validate(['decision_note' => ['nullable', 'string', 'max:2000']])['decision_note'] ?? null;
        $action->handle($viewRequest, $request->user(), $note);

        return back()->with('success', 'Access approved and conversation opened.');
    }

    public function decline(Request $request, ViewRequest $viewRequest): RedirectResponse
    {
        abort_unless($request->user()->can('decide', $viewRequest), 403);
        abort_unless($viewRequest->status === 'pending', 422);
        $note = $request->validate(['decision_note' => ['nullable', 'string', 'max:2000']])['decision_note'] ?? null;
        $viewRequest->update(['status' => 'declined', 'decision_note' => $note, 'decided_at' => now(), 'decided_by' => $request->user()->id]);
        AuditService::record('access.declined', $viewRequest);
        $viewRequest->requester->notify(new MarketplaceNotification('Viewing request declined', 'Your request for '.$viewRequest->project->title.' was declined.', '/view-requests'));

        return back()->with('success', 'Request declined.');
    }

    public function revoke(Request $request, ViewRequest $viewRequest): RedirectResponse
    {
        abort_unless($request->user()->can('decide', $viewRequest), 403);
        abort_unless($viewRequest->status === 'approved', 422);

        DB::transaction(function () use ($request, $viewRequest) {
            AccessGrant::where('view_request_id', $viewRequest->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $viewRequest->forceFill(['status' => 'revoked', 'decided_at' => now(), 'decided_by' => $request->user()->id])->save();
            AuditService::record('access.revoked', $viewRequest, ['project_id' => $viewRequest->project_id]);
            $viewRequest->requester->notify(new MarketplaceNotification(
                'Project access revoked',
                'Restricted access to '.$viewRequest->project->title.' has been revoked.',
                '/view-requests',
                'warning'
            ));
        });

        return back()->with('success', 'Project access revoked. Historical conversation messages remain available to their participants.');
    }
}
