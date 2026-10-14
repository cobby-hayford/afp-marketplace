<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FestivalRecognition;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FestivalRecognitionController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->can('update', $project), 403);
        $data = $request->validate([
            'festival_name' => ['required', 'string', 'max:200'],
            'festival_country_id' => ['nullable', 'exists:countries,id'],
            'year' => ['nullable', 'integer', 'between:1900,'.(now()->year + 2)],
            'selection_type' => ['nullable', 'string', 'max:120'],
            'award_category' => ['nullable', 'string', 'max:180'],
            'evidence_url' => ['nullable', 'url', 'max:2048'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        unset($data['evidence']);
        $data['project_id'] = $project->id;
        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('festival-evidence/'.$project->id, 'private');
        }
        FestivalRecognition::create($data);

        return back()->with('success', 'Festival recognition added as self-declared pending verification.');
    }

    public function destroy(Request $request, Project $project, FestivalRecognition $festival): RedirectResponse
    {
        abort_unless($request->user()->can('update', $project), 403);
        abort_unless($festival->project_id === $project->id, 404);
        if ($festival->evidence_path) {
            Storage::disk('private')->delete($festival->evidence_path);
        }
        $festival->delete();

        return back()->with('success', 'Festival recognition removed.');
    }
}
