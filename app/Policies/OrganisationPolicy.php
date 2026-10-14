<?php

namespace App\Policies;

use App\Models\Organisation;
use App\Models\User;

class OrganisationPolicy
{
    public function update(User $u, Organisation $o): bool
    {
        return ! $u->suspended_at && $o->managedBy($u);
    }

    public function delete(User $u, Organisation $o): bool
    {
        return $this->update($u, $o);
    }
}
