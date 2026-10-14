<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversation}', function (User $user, Conversation $conversation) {
    return ! $user->suspended_at && $conversation->hasParticipant($user);
});
Broadcast::channel('user.{id}', fn (User $user, int $id) => $user->id === $id);
