<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveProject;
use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use App\Models\Project;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use App\Services\MarketplacePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectModerationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Project::with(['owner.profile', 'country', 'contentType', 'stage']);
        if ($request->filled('status')) {
            $query->where('publication_status', $request->query('status'));
        }

        return Inertia::render('admin/projects', [
            'projects' => $query->latest()->paginate(25)->withQueryString(),
            'filters' => $request->query(),
        ]);
    }

    public function show(Project $project, Request $request): Response
    {
        $project->load([
            'owner.profile', 'country', 'contentType', 'stage', 'genres', 'tags', 'languages',
            'coProductionCountries', 'teamMembers.profile.user', 'media', 'documents', 'rights',
            'festivalRecognitions.country', 'stand', 'accessGrants',
        ]);

        return Inertia::render('admin/project-preview', [
            'project' => MarketplacePresenter::projectDetail($project, $request->user()),
        ]);
    }

    public function approve(Request $request, Project $project, ApproveProject $action): RedirectResponse
    {
        $comments = $request->validate(['comments' => ['nullable', 'string', 'max:3000']])['comments'] ?? null;
        $action->handle($project, $request->user(), $comments);

        return back()->with('success', 'Project published.');
    }

    public function transition(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:changes_requested,rejected,suspended,archived'],
            'comments' => ['required', 'string', 'max:3000'],
        ]);

        $allowed = match ($project->publication_status) {
            'submitted', 'under_review' => ['changes_requested', 'rejected', 'suspended', 'archived'],
            'approved', 'published' => ['suspended', 'archived'],
            default => [],
        };
        abort_unless(in_array($data['status'], $allowed, true), 422);

        $before = $project->publication_status;
        $project->forceFill([
            'publication_status' => $data['status'],
            'published_at' => null,
            'featured' => false,
        ])->save();
        ModerationAction::create([
            'moderatable_type' => $project->getMorphClass(),
            'moderatable_id' => $project->id,
            'actor_id' => $request->user()->id,
            'previous_status' => $before,
            'new_status' => $data['status'],
            'comments' => $data['comments'],
        ]);
        AuditService::record('project.'.$data['status'], $project, ['previous_status' => $before]);
        $project->owner->notify(new MarketplaceNotification(
            'Project status updated',
            $project->title.': '.$data['comments'],
            '/my-projects/'.$project->slug.'/edit'
        ));

        return back()->with('success', 'Project status updated.');
    }

    public function feature(Project $project): RedirectResponse
    {
        abort_unless($project->publication_status === 'published' && $project->published_at?->lte(now()), 422);
        $project->update(['featured' => ! $project->featured]);
        AuditService::record($project->featured ? 'project.featured' : 'project.unfeatured', $project);

        return back();
    }

    public function unpublish(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->publication_status === 'published', 422);
        $before = $project->publication_status;
        $project->forceFill(['publication_status' => 'approved', 'published_at' => null, 'featured' => false])->save();
        ModerationAction::create([
            'moderatable_type' => $project->getMorphClass(),
            'moderatable_id' => $project->id,
            'actor_id' => $request->user()->id,
            'previous_status' => $before,
            'new_status' => 'approved',
            'comments' => 'Listing unpublished by administrator.',
        ]);
        AuditService::record('project.unpublished', $project);
        $project->owner->notify(new MarketplaceNotification(
            'Project unpublished',
            $project->title.' has been removed from public discovery by an administrator.',
            '/my-projects/'.$project->slug.'/edit',
            'warning'
        ));

        return back()->with('success', 'Project unpublished.');
    }
}
