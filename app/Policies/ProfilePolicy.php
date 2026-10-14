<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    public function update(User $u, Profile $p): bool
    {
        return ! $u->suspended_at && ($p->user_id === $u->id || $u->isAdmin());
    }
}
