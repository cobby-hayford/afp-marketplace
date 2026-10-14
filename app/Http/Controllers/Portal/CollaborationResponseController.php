<?php

namespace App\Http\Controllers\Portal;

use App\Actions\RespondToCollaboration;
use App\Http\Controllers\Controller;
use App\Models\CollaborationPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CollaborationResponseController extends Controller
{
    public function store(Request $r, CollaborationPost $collaboration, RespondToCollaboration $action): RedirectResponse
    {
        $data = $r->validate(['message' => ['nullable', 'string', 'max:3000']]);
        $action->handle($collaboration, $r->user(), $data['message'] ?? null);

        return back()->with('success', 'Connection request sent.');
    }
}
