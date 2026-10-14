<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    public function download(Request $request, ProjectDocument $document): StreamedResponse
    {
        $project = $document->project;
        $user = $request->user();
        $published = $project->publication_status === 'published' && $project->published_at?->lte(now());
        $approvedMember = $user && ! $user->suspended_at && $user->hasVerifiedEmail() && $user->profile?->status === 'approved';
        $allowed = match ($document->access_level) {
            'public' => $published && $project->visibility === 'public',
            'authenticated' => $published && (bool) $user && ! $user->suspended_at
                && ($project->visibility === 'public' || ($project->visibility === 'members' && $approvedMember)),
            'request_required' => (bool) ($user && ! $user->suspended_at && $project->hasAccess($user)),
            'private' => (bool) ($user && ! $user->suspended_at && $project->isManagedBy($user)),
            default => false,
        };
        abort_unless($allowed, 403);
        abort_unless(Storage::disk('private')->exists($document->path), 404);

        return Storage::disk('private')->download($document->path, $document->title);
    }

    public function update(Request $request, Project $project, ProjectDocument $document): RedirectResponse
    {
        abort_unless($document->project_id === $project->id && $project->isManagedBy($request->user()), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['pitch_deck', 'press_kit', 'sales_sheet', 'brochure', 'director_statement', 'producer_statement', 'treatment', 'financing_plan', 'other'])],
            'access_level' => ['required', Rule::in(['public', 'authenticated', 'request_required', 'private'])],
        ]);
        $document->update($data);

        return back()->with('success', 'Document settings updated.');
    }

    public function destroy(Request $request, Project $project, ProjectDocument $document): RedirectResponse
    {
        abort_unless($document->project_id === $project->id && $project->isManagedBy($request->user()), 403);
        Storage::disk('private')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }
}
