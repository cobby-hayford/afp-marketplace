<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('portal/settings', ['account' => $request->user()->only(['first_name', 'last_name', 'email', 'country_id', 'account_type']), 'countries' => Country::orderBy('name')->get(['id', 'name'])]);
    }

    public function update(Request $request): RedirectResponse
    {
        $u = $request->user();
        $data = $request->validate(['first_name' => 'required|string|max:100', 'last_name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users,email,'.$u->id, 'country_id' => 'required|exists:countries,id']);
        $emailChanged = $data['email'] !== $u->email;
        $u->fill($data);
        if ($emailChanged) {
            $u->email_verified_at = null;
        }$u->save();
        if ($emailChanged) {
            $u->sendEmailVerificationNotification();
        }

return back()->with('success', $emailChanged ? 'Account updated. Please verify your new email address.' : 'Account updated.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()]]);
        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password updated.');
    }
}
