<?php

namespace Tests\Feature;

use App\Models\Project;
use Tests\TestCase;

class ProjectWorkflowTest extends TestCase
{
    public function test_approved_member_creates_submits_and_admin_publishes_project(): void
    {
        $owner = $this->approvedUser();
        $this->actingAs($owner)->post('/my-projects', $this->projectData(['title' => 'Tindana Test']))->assertRedirect();
        $project = Project::where('title', 'Tindana Test')->firstOrFail();
        $this->assertSame('draft', $project->publication_status);
        $this->get("/projects/{$project->slug}")->assertNotFound();

        $this->actingAs($owner)->post("/my-projects/{$project->slug}/submit", ['copyright_declaration' => true])->assertSessionHasNoErrors();
        $this->assertSame('submitted', $project->fresh()->publication_status);
        $this->assertDatabaseHas('copyright_declarations', ['project_id' => $project->id, 'user_id' => $owner->id]);

        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/projects/{$project->slug}/approve", ['comments' => 'Approved for trade hall.'])->assertSessionHasNoErrors();
        $this->assertSame('published', $project->fresh()->publication_status);
        $this->get("/projects/{$project->slug}")->assertOk();
    }

    public function test_rejected_or_draft_project_is_not_public(): void
    {
        $owner = $this->approvedUser();
        foreach (['draft', 'rejected'] as $status) {
            $project = Project::factory()->create(['owner_id' => $owner->id, 'publication_status' => $status, 'published_at' => null]);
            $this->get("/projects/{$project->slug}")->assertNotFound();
        }
    }

    public function test_user_cannot_edit_another_users_project(): void
    {
        $owner = $this->approvedUser();
        $other = $this->approvedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $this->actingAs($other)->put("/my-projects/{$project->slug}", $this->projectData(['title' => 'Hijacked']))->assertForbidden();
        $this->assertNotSame('Hijacked', $project->fresh()->title);
    }

    public function test_editing_published_project_returns_it_to_review(): void
    {
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner);
        $this->actingAs($owner)->put("/my-projects/{$project->slug}", $this->projectData(['title' => $project->title]))->assertSessionHasNoErrors();
        $this->assertSame('under_review', $project->fresh()->publication_status);
    }

    public function test_owner_can_soft_delete_a_draft_project_but_not_a_published_listing(): void
    {
        $owner = $this->approvedUser();
        $draft = Project::factory()->create(['owner_id' => $owner->id, 'publication_status' => 'draft', 'published_at' => null]);
        $this->actingAs($owner)->delete("/my-projects/{$draft->slug}")->assertRedirect('/my-projects');
        $this->assertSoftDeleted('projects', ['id' => $draft->id]);

        $published = $this->publishedProject($owner);
        $this->delete("/my-projects/{$published->slug}")->assertStatus(422);
        $this->assertNotSoftDeleted('projects', ['id' => $published->id]);
    }

    public function test_admin_can_unpublish_a_published_project(): void
    {
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner);
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/admin/projects/{$project->slug}/unpublish")->assertSessionHasNoErrors();
        $project->refresh();
        $this->assertSame('approved', $project->publication_status);
        $this->assertNull($project->published_at);
        $this->get("/projects/{$project->slug}")->assertNotFound();
        $this->assertDatabaseHas('audit_logs', ['action' => 'project.unpublished']);
    }

    public function test_admin_cannot_publish_a_draft_project_by_replaying_the_approval_route(): void
    {
        $owner = $this->approvedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id, 'publication_status' => 'draft', 'published_at' => null]);
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/projects/{$project->slug}/approve")->assertStatus(422);
        $this->assertSame('draft', $project->fresh()->publication_status);
    }

    public function test_admin_can_suspend_a_published_project_and_it_immediately_leaves_public_discovery(): void
    {
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner, ['featured' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/projects/{$project->slug}/transition", [
            'status' => 'suspended',
            'comments' => 'Temporarily removed pending review.',
        ])->assertSessionHasNoErrors();

        $project->refresh();
        $this->assertSame('suspended', $project->publication_status);
        $this->assertNull($project->published_at);
        $this->assertFalse($project->featured);
        $this->get("/projects/{$project->slug}")->assertNotFound();
    }
}
