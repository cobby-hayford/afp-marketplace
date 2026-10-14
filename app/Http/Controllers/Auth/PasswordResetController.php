<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    public function request(): Response
    {
        return Inertia::render('auth/forgot-password', ['status' => session('status')]);
    }

    public function email(Request $r): RedirectResponse
    {
        $r->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($r->only('email'));

        return $status === Password::RESET_LINK_SENT ? back()->with('status', __($status)) : back()->withErrors(['email' => __($status)]);
    }

    public function resetForm(Request $r, string $token): Response
    {
        return Inertia::render('auth/reset-password', ['email' => $r->email, 'token' => $token]);
    }

    public function reset(Request $r): RedirectResponse
    {
        $r->validate(['token' => 'required', 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->mixedCase()->numbers()]]);
        $status = Password::reset($r->only('email', 'password', 'password_confirmation', 'token'), function (User $u, string $password) {
            $u->forceFill(['password' => $password])->setRememberToken(Str::random(60));
            $u->save();
            event(new PasswordReset($u));
        });

        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('status', __($status)) : back()->withErrors(['email' => __($status)]);
    }
}
