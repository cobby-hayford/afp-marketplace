<?php

namespace App\Http\Controllers\Portal;

use App\Events\MessageCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Models\Conversation;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function index(Request $r): Response
    {
        return Inertia::render('portal/messages/index', ['conversations' => Conversation::whereHas('participants', fn ($q) => $q->where('users.id', $r->user()->id))->with(['participants:id,first_name,last_name', 'messages' => fn ($q) => $q->latest()->limit(1)])->orderByDesc('last_message_at')->paginate(20)]);
    }

    public function show(Request $r, Conversation $conversation): Response
    {
        abort_unless($r->user()->can('view', $conversation), 403);
        $conversation->participants()->updateExistingPivot($r->user()->id, ['last_read_at' => now()]);
        $conversation->load('participants:id,first_name,last_name');

        return Inertia::render('portal/messages/show', ['conversation' => $conversation->only(['id', 'type', 'last_message_at']) + ['participants' => $conversation->participants->map->only(['id', 'first_name', 'last_name'])], 'messages' => $conversation->messages()->with('sender:id,first_name,last_name')->oldest()->paginate(50)]);
    }

    public function store(MessageRequest $r, Conversation $conversation): RedirectResponse
    {
        abort_unless($r->user()->can('send', $conversation), 403);
        $m = $conversation->messages()->create(['sender_id' => $r->user()->id, 'body' => $r->validated('body')]);
        $conversation->update(['last_message_at' => now()]);
        $m->load('sender');
        event(new MessageCreated($m));
        $conversation->participants()->where('users.id', '!=', $r->user()->id)->each(fn ($u) => $u->notify(new MarketplaceNotification('New private message', $r->user()->name.' sent you a message.', '/messages/'.$conversation->id, 'message')));

        return back();
    }
}
