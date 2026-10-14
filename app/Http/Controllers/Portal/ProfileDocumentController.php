<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ProfileDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileDocumentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->profile;
        abort_unless($profile, 404);
        $data = $request->validate([
            'type' => ['required', Rule::in(['association_proof', 'identity_document', 'professional_document', 'company_document', 'other'])],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:10240'],
        ]);
        $file = $request->file('document');
        $path = $file->store('profile-documents/'.$profile->id, 'private');
        $profile->documents()->create([
            'type' => $data['type'],
            'title' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Supporting document uploaded.');
    }

    public function download(Request $request, ProfileDocument $document): StreamedResponse
    {
        $allowed = $request->user() && ($request->user()->id === $document->profile->user_id || $request->user()->isAdmin());
        abort_unless($allowed, 403);
        abort_unless(Storage::disk('private')->exists($document->path), 404);

        return Storage::disk('private')->download($document->path, $document->title);
    }

    public function destroy(Request $request, ProfileDocument $document): RedirectResponse
    {
        abort_unless($request->user()->id === $document->profile->user_id, 403);
        abort_if($document->profile->status === 'approved', 422, 'Approved-profile documents cannot be removed without reopening the application.');
        Storage::disk('private')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Supporting document removed.');
    }
}
