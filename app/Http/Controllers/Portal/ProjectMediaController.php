<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectMediaController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->can('update', $project), 403);
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'],
            'caption' => ['nullable', 'string', 'max:300'],
        ]);

        ProjectMedia::create([
            'project_id' => $project->id,
            'type' => 'gallery_image',
            'path' => $request->file('image')->store('projects/gallery/'.$project->id, 'public'),
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) $project->media()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Gallery image added.');
    }

    public function destroy(Request $request, Project $project, ProjectMedia $media): RedirectResponse
    {
        abort_unless($request->user()->can('update', $project), 403);
        abort_unless($media->project_id === $project->id, 404);
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return back()->with('success', 'Gallery image removed.');
    }
}
