<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with(['country', 'profile']);
        if ($search = trim((string) $request->query('q'))) {
            $like = '%'.mb_strtolower($search).'%';
            $query->where(fn ($q) => $q
                ->whereRaw('LOWER(email) LIKE ?', [$like])
                ->orWhereRaw("LOWER(first_name || ' ' || last_name) LIKE ?", [$like]));
        }

        return Inertia::render('admin/users', [
            'users' => $query->latest()->paginate(30)->withQueryString(),
            'filters' => $request->query(),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load([
            'country:id,name,iso_code',
            'profile.country:id,name',
            'profile.roles',
            'profile.genres:id,name',
            'profile.languages:id,name',
            'profile.contentTypes:id,name',
            'projects:id,owner_id,title,slug,publication_status,updated_at',
            'organisations:id,created_by,name,slug,approval_status',
            'adminNotes' => fn ($query) => $query->with('actor:id,first_name,last_name')->latest(),
        ]);
        $user->profile?->makeVisible('phone');

        return Inertia::render('admin/user-show', ['managedUser' => $user]);
    }

    public function role(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('manage-admin-roles'), 403);
        $data = $request->validate(['role' => ['required', 'in:member,moderator,administrator,super_administrator']]);
        $old = $user->role;
        $user->forceFill(['role' => $data['role']])->save();
        AuditService::record('user.role_changed', $user, ['from' => $old, 'to' => $data['role']]);

        return back()->with('success', 'Role updated.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->forceFill(['suspended_at' => null])->save();

        if ($user->profile?->status === 'suspended') {
            $restoredStatus = $user->profile->status_before_suspension ?: 'draft';
            $user->profile->forceFill([
                'status' => $restoredStatus,
                'status_before_suspension' => null,
                'is_public' => $restoredStatus === 'approved',
            ])->save();
        }

        AuditService::record('user.restored', $user);

        return back()->with('success', 'User restored.');
    }
}
