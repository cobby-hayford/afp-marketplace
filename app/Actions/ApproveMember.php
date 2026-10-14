<?php

namespace App\Actions;

use App\Models\ModerationAction;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class ApproveMember
{
    public function handle(Profile $profile, User $admin, ?string $comments = null): Profile
    {
        return DB::transaction(function () use ($profile, $admin, $comments) {
            abort_unless(in_array($profile->status, ['submitted', 'pending_review'], true), 422);
            $before = $profile->status;
            $profile->forceFill(['status' => 'approved', 'is_public' => true, 'approved_at' => now()])->save();
            ModerationAction::create(['moderatable_type' => $profile->getMorphClass(), 'moderatable_id' => $profile->id, 'actor_id' => $admin->id, 'previous_status' => $before, 'new_status' => 'approved', 'comments' => $comments]);
            AuditService::record('member.approved', $profile, ['previous_status' => $before]);
            $profile->user->notify(new MarketplaceNotification('Membership approved', 'Your professional profile has been approved and can now appear in the marketplace.', '/dashboard', 'success'));

            return $profile->fresh();
        });
    }
}
