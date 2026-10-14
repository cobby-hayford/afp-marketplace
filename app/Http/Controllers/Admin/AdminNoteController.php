<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminNoteController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user->adminNotes()->create([
            'actor_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        AuditService::record('user.internal_note_added', $user);

        return back()->with('success', 'Internal note added.');
    }
}
