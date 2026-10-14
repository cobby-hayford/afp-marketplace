<?php

namespace Tests\Feature;

use App\Models\AccessGrant;
use App\Models\Conversation;
use App\Models\ViewRequest;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ViewRequestAndMessagingTest extends TestCase
{
    public function test_request_to_view_creates_grant_and_private_conversation_after_owner_approval(): void
    {
        $owner = $this->approvedUser('producer');
        $project = $this->publishedProject($owner, ['screener_url' => 'https://secure.example.test/screener/abc']);
        $buyer = $this->approvedUser('distributor');

        $this->actingAs($buyer)->get("/projects/{$project->slug}")->assertDontSee('secure.example.test/screener/abc', false);
        $this->post("/projects/{$project->slug}/view-requests", [
            'position' => 'Acquisitions Director', 'interest_type' => 'Acquisition', 'territory' => 'West Africa', 'reason' => 'Acquisition review',
        ])->assertSessionHasNoErrors();
        $viewRequest = ViewRequest::where('project_id', $project->id)->where('requester_id', $buyer->id)->firstOrFail();

        $random = $this->approvedUser();
        $this->actingAs($random)->post("/view-requests/{$viewRequest->id}/approve")->assertForbidden();
        $this->actingAs($owner)->post("/view-requests/{$viewRequest->id}/approve")->assertSessionHasNoErrors();

        $this->assertDatabaseHas('access_grants', ['project_id' => $project->id, 'user_id' => $buyer->id]);
        $conversation = Conversation::where('type', 'view_request')->firstOrFail();
        $this->assertTrue($conversation->hasParticipant($owner));
        $this->assertTrue($conversation->hasParticipant($buyer));
        $this->actingAs($buyer)
            ->get("/projects/{$project->slug}")
            ->assertInertia(fn (Assert $page) => $page
                ->where(
                    'project.restricted.screener_url',
                    'https://secure.example.test/screener/abc'
                )
            );
    }

    public function test_only_conversation_participants_can_read_and_send_messages(): void
    {
        $owner = $this->approvedUser();
        $buyer = $this->approvedUser('distributor');
        $project = $this->publishedProject($owner);
        $request = ViewRequest::create(['requester_id' => $buyer->id, 'project_id' => $project->id, 'position' => 'Buyer', 'interest_type' => 'Acquisition', 'reason' => 'Review', 'status' => 'pending']);
        $this->actingAs($owner)->post("/view-requests/{$request->id}/approve");
        $conversation = Conversation::firstOrFail();

        $this->actingAs($buyer)->get("/messages/{$conversation->id}")->assertOk();
        $this->post("/messages/{$conversation->id}", ['body' => 'We would like to discuss rights.'])->assertRedirect();
        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'sender_id' => $buyer->id, 'body' => 'We would like to discuss rights.']);

        $random = $this->approvedUser();
        $this->actingAs($random)->get("/messages/{$conversation->id}")->assertForbidden();
        $this->post("/messages/{$conversation->id}", ['body' => 'Unauthorized'])->assertForbidden();
    }

    public function test_revocation_disables_restricted_access(): void
    {
        $owner = $this->approvedUser();
        $buyer = $this->approvedUser('distributor');
        $project = $this->publishedProject($owner, ['screener_url' => 'https://secure.example.test/revoked']);
        $request = ViewRequest::create(['requester_id' => $buyer->id, 'project_id' => $project->id, 'position' => 'Buyer', 'interest_type' => 'Licensing', 'reason' => 'Review', 'status' => 'pending']);
        $this->actingAs($owner)->post("/view-requests/{$request->id}/approve");
        $this->assertTrue($project->fresh()->hasAccess($buyer));
        $this->post("/view-requests/{$request->id}/revoke")->assertSessionHasNoErrors();
        $this->assertFalse($project->fresh()->hasAccess($buyer));
        $this->assertNotNull(AccessGrant::where('project_id', $project->id)->firstOrFail()->revoked_at);
    }
}
