<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    public function test_member_is_denied_admin_routes_and_admin_can_access(): void
    {
        $member = $this->approvedUser();
        $this->actingAs($member)->get('/admin')->assertForbidden();
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_only_super_administrator_can_change_admin_roles(): void
    {
        $admin = $this->admin('administrator');
        $target = $this->approvedUser();
        $this->actingAs($admin)->patch("/admin/users/{$target->id}/role", ['role' => 'moderator'])->assertForbidden();
        $super = $this->admin('super_administrator');
        $this->actingAs($super)->patch("/admin/users/{$target->id}/role", ['role' => 'moderator'])->assertSessionHasNoErrors();
        $this->assertSame('moderator', $target->fresh()->role);
    }

    public function test_admin_can_add_private_internal_note_to_user(): void
    {
        $admin = $this->admin();
        $target = $this->approvedUser();

        $this->actingAs($admin)->post("/admin/users/{$target->id}/notes", [
            'body' => 'Association documentation checked; follow up on company verification.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admin_notes', [
            'notable_type' => $target->getMorphClass(),
            'notable_id' => $target->id,
            'actor_id' => $admin->id,
        ]);
    }
}
