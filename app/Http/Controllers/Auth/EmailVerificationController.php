<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function notice(Request $r): Response|RedirectResponse
    {
        return $r->user()->hasVerifiedEmail() ? redirect()->route('dashboard') : Inertia::render('auth/verify-email', ['status' => session('status')]);
    }

    public function verify(EmailVerificationRequest $r): RedirectResponse
    {
        $r->fulfill();

        return redirect()->route('profile.edit')->with('success', 'Email verified. Complete your professional profile.');
    }

    public function send(Request $r): RedirectResponse
    {
        if ($r->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }$r->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
