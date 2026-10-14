<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $r): Response
    {
        return Inertia::render('portal/notifications', ['notifications' => $r->user()->notifications()->latest()->paginate(30)]);
    }

    public function read(Request $r, string $id): RedirectResponse
    {
        $n = $r->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        return back();
    }

    public function readAll(Request $r): RedirectResponse
    {
        $r->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notifications marked as read.');
    }
}
