<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ViewRequest;

class ViewRequestPolicy
{
    public function view(User $u, ViewRequest $v): bool
    {
        return $v->requester_id === $u->id || $v->project->isManagedBy($u);
    }

    public function decide(User $u, ViewRequest $v): bool
    {
        return ! $u->suspended_at && $v->project->isManagedBy($u);
    }
}
