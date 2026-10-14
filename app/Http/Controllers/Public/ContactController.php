<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('public/contact', [
            'seo' => [
                'title' => 'Contact | African Film Producers',
                'description' => 'Contact the African Film Producers Content Marketplace.',
                'canonical' => url('/contact'),
            ],
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $submission = ContactSubmission::create([
            ...$request->validated(),
            'ip_address' => $request->ip(),
        ]);

        $recipient = trim((string) (SiteSettings::all()['contact_email'] ?? ''));
        if ($recipient !== '') {
            Mail::to($recipient)->queue(new ContactSubmissionReceived($submission));
        }

        return back()->with('success', 'Thank you. Your message has been received.');
    }
}
