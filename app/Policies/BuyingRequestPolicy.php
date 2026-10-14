<?php

namespace App\Policies;

use App\Models\BuyingRequest;
use App\Models\User;

class BuyingRequestPolicy
{
    public function create(User $user): bool
    {
        return ! $user->suspended_at && $user->canPublishBuyingRequests();
    }

    public function update(User $user, BuyingRequest $request): bool
    {
        return ! $user->suspended_at
            && $user->canPublishBuyingRequests()
            && $request->user_id === $user->id;
    }
}
