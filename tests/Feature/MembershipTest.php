<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Profile;
use App\Models\User;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    public function test_verified_user_can_submit_complete_profile(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::factory()->create([
            'user_id' => $user->id, 'country_id' => Country::where('iso_code', 'GH')->value('id'),
            'status' => 'draft', 'is_public' => false,
        ]);
        $this->actingAs($user)->post('/profile/submit')->assertSessionHasNoErrors();
        $this->assertSame('pending_review', $profile->fresh()->status);
    }

    public function test_admin_can_approve_member_and_history_is_recorded(): void
    {
        $applicant = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $applicant->id, 'status' => 'pending_review', 'is_public' => false]);
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/memberships/{$profile->slug}/approve", ['comments' => 'Validated.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('profiles', ['id' => $profile->id, 'status' => 'approved', 'is_public' => 1]);
        $this->assertDatabaseHas('moderation_actions', ['moderatable_id' => $profile->id, 'new_status' => 'approved']);
    }

    public function test_ordinary_member_cannot_approve_member(): void
    {
        $member = $this->approvedUser();
        $applicant = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $applicant->id, 'status' => 'pending_review']);
        $this->actingAs($member)->post("/admin/memberships/{$profile->slug}/approve")->assertForbidden();
    }

    public function test_suspended_member_is_blocked_from_portal(): void
    {
        $user = $this->approvedUser();
        $user->forceFill(['suspended_at' => now()])->save();
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_restoring_suspended_user_preserves_previous_profile_approval_state(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::factory()->create(['user_id' => $user->id, 'status' => 'pending_review', 'is_public' => false]);
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/admin/users/{$user->id}/suspend")->assertSessionHasNoErrors();
        $this->assertSame('suspended', $profile->fresh()->status);
        $this->assertSame('pending_review', $profile->fresh()->status_before_suspension);

        $this->patch("/admin/users/{$user->id}/restore")->assertSessionHasNoErrors();
        $profile->refresh();
        $this->assertSame('pending_review', $profile->status);
        $this->assertFalse($profile->is_public);
        $this->assertNull($profile->status_before_suspension);
    }
}
