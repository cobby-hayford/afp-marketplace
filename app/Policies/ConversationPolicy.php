<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $u, Conversation $c): bool
    {
        return ! $u->suspended_at && $c->hasParticipant($u);
    }

    public function send(User $u, Conversation $c): bool
    {
        return $this->view($u, $c);
    }
}
