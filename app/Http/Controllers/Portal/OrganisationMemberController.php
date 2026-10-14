<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganisationMemberController extends Controller
{
    public function store(Request $request, Organisation $organisation): RedirectResponse
    {
        abort_unless($organisation->managedBy($request->user()), 403);
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', Rule::in(['manager', 'team_member'])],
        ]);
        $user = User::where('email', $data['email'])->firstOrFail();
        abort_if($user->suspended_at, 422, 'Suspended users cannot be added to an organisation.');
        $organisation->members()->syncWithoutDetaching([$user->id => ['role' => $data['role']]]);
        $organisation->members()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return back()->with('success', 'Organisation member added or updated.');
    }

    public function destroy(Request $request, Organisation $organisation, User $user): RedirectResponse
    {
        abort_unless($organisation->managedBy($request->user()), 403);
        abort_if($user->id === $organisation->created_by, 422, 'The organisation owner cannot be removed.');
        $organisation->members()->detach($user->id);

        return back()->with('success', 'Organisation member removed.');
    }
}
