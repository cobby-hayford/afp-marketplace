<?php

namespace App\Policies;

use App\Models\CollaborationPost;
use App\Models\User;

class CollaborationPostPolicy
{
    public function update(User $u, CollaborationPost $p): bool
    {
        return ! $u->suspended_at && ($p->user_id === $u->id || $u->isAdmin());
    }
}
