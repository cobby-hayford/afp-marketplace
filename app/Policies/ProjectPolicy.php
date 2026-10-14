<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $u, Project $p): bool
    {
        return $p->publication_status === 'published' || $p->isManagedBy($u);
    }

    public function create(User $u): bool
    {
        return ! $u->suspended_at && $u->hasVerifiedEmail() && $u->profile?->status === 'approved';
    }

    public function update(User $u, Project $p): bool
    {
        return ! $u->suspended_at && $p->isManagedBy($u);
    }

    public function delete(User $u, Project $p): bool
    {
        return $this->update($u, $p);
    }

    public function submit(User $u, Project $p): bool
    {
        return $this->update($u, $p) && in_array($p->publication_status, ['draft', 'changes_requested', 'rejected'], true);
    }
}
