<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmPasswordController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/confirm-password');
    }

    public function store(Request $r): RedirectResponse
    {
        if (! Hash::check($r->validate(['password' => ['required', 'string']])['password'], $r->user()->password)) {
            throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
        }$r->session()->passwordConfirmed();

        return redirect()->intended(route('dashboard'));
    }
}
