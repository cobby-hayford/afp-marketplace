<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectTeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectTeamMemberController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->can('update', $project), 403);
        $data = $request->validate([
            'profile_id' => ['nullable', 'integer', 'exists:profiles,id'],
            'external_name' => ['nullable', 'string', 'max:180'],
            'role' => ['required', 'string', 'max:120'],
        ]);

        if (empty($data['profile_id']) && blank($data['external_name'] ?? null)) {
            return back()->withErrors(['external_name' => 'Choose a registered profile or enter an external team member name.']);
        }
        if (! empty($data['profile_id'])) {
            $profile = Profile::public()->findOrFail($data['profile_id']);
            $data['external_name'] = null;
        }

        $data['project_id'] = $project->id;
        $data['sort_order'] = (int) $project->teamMembers()->max('sort_order') + 1;
        ProjectTeamMember::create($data);

        return back()->with('success', 'Project team member added.');
    }

    public function destroy(Request $request, Project $project, ProjectTeamMember $teamMember): RedirectResponse
    {
        abort_unless($request->user()->can('update', $project), 403);
        abort_unless($teamMember->project_id === $project->id, 404);
        $teamMember->delete();

        return back()->with('success', 'Project team member removed.');
    }
}
