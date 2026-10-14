<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectRight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectRightController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->isManagedBy($request->user()), 403);
        $data = $request->validate([
            'right_type' => ['required', 'string', 'max:120'],
            'territory' => ['nullable', 'string', 'max:160'],
            'availability' => ['required', Rule::in(['available', 'negotiable', 'reserved', 'unavailable'])],
        ]);
        $project->rights()->create($data);

        return back()->with('success', 'Commercial right added.');
    }

    public function destroy(Request $request, Project $project, ProjectRight $right): RedirectResponse
    {
        abort_unless($right->project_id === $project->id && $project->isManagedBy($request->user()), 403);
        $right->delete();

        return back()->with('success', 'Commercial right removed.');
    }
}
