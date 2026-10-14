<?php

namespace App\Actions;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StartConversation
{
    public function handle(string $type, Model $context, User $creator, array $participants): Conversation
    {
        $conversation = Conversation::firstOrCreate(['type' => $type, 'context_type' => $context->getMorphClass(), 'context_id' => $context->getKey()], ['created_by' => $creator->id]);
        $ids = collect($participants)->push($creator)->map(fn ($u) => $u instanceof User ? $u->id : $u)->unique()->values()->all();
        foreach ($ids as $id) {
            $conversation->participants()->syncWithoutDetaching([$id]);
        }

return $conversation->fresh('participants');
    }
}
