<?php

namespace App\Policies;

use App\Models\Stand;
use App\Models\User;

class StandPolicy
{
    public function update(User $u, Stand $s): bool
    {
        return ! $u->suspended_at && ($s->created_by === $u->id || ($s->organisation && $s->organisation->managedBy($u)) || $u->isAdmin());
    }
}
