<?php

namespace Tests\Feature;

use App\Models\AccessGrant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Organisation;
use App\Models\ProfileDocument;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    public function test_private_project_document_cannot_be_downloaded_by_unauthorized_user(): void
    {
        Storage::fake('private');
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner);
        Storage::disk('private')->put('project-documents/secret.pdf', 'classified');
        $document = ProjectDocument::create(['project_id' => $project->id, 'type' => 'pitch_deck', 'title' => 'Secret Deck.pdf', 'path' => 'project-documents/secret.pdf', 'mime_type' => 'application/pdf', 'size' => 10, 'access_level' => 'private']);

        $this->get("/project-documents/{$document->id}/download")->assertForbidden();
        $random = $this->approvedUser();
        $this->actingAs($random)->get("/project-documents/{$document->id}/download")->assertForbidden();
        $this->actingAs($owner)->get("/project-documents/{$document->id}/download")->assertOk();
    }

    public function test_request_required_document_is_unavailable_until_access_grant_exists(): void
    {
        Storage::fake('private');
        $owner = $this->approvedUser();
        $viewer = $this->approvedUser('distributor');
        $project = $this->publishedProject($owner);
        Storage::disk('private')->put('project-documents/request.pdf', 'restricted');
        $document = ProjectDocument::create(['project_id' => $project->id, 'type' => 'sales_sheet', 'title' => 'Sales Sheet.pdf', 'path' => 'project-documents/request.pdf', 'mime_type' => 'application/pdf', 'size' => 10, 'access_level' => 'request_required']);
        $this->actingAs($viewer)->get("/project-documents/{$document->id}/download")->assertForbidden();
        AccessGrant::create(['project_id' => $project->id, 'user_id' => $viewer->id, 'granted_by' => $owner->id, 'granted_at' => now()]);
        $this->get("/project-documents/{$document->id}/download")->assertOk();
    }

    public function test_restricted_screener_url_never_appears_for_unauthorized_viewer(): void
    {
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner, ['screener_url' => 'https://secret.example.test/full-screener']);
        $viewer = $this->approvedUser('distributor');
        $this->actingAs($viewer)->get("/projects/{$project->slug}")->assertOk()->assertDontSee('secret.example.test/full-screener', false);
    }

    public function test_requester_cannot_claim_unmanaged_organisation_in_view_request(): void
    {
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner);
        $viewer = $this->approvedUser('distributor');
        $other = $this->approvedUser('broadcaster');
        $organisation = Organisation::factory()->create(['created_by' => $other->id, 'approval_status' => 'approved']);

        $this->actingAs($viewer)->post("/projects/{$project->slug}/view-requests", [
            'organisation_id' => $organisation->id, 'position' => 'Buyer', 'interest_type' => 'Acquisition', 'reason' => 'Review',
        ])->assertForbidden();
    }

    public function test_private_profile_supporting_document_is_owner_or_admin_only(): void
    {
        Storage::fake('private');
        $owner = $this->approvedUser();
        Storage::disk('private')->put('profile-documents/id.pdf', 'identity');
        $document = ProfileDocument::create(['profile_id' => $owner->profile->id, 'type' => 'identity_document', 'title' => 'ID.pdf', 'path' => 'profile-documents/id.pdf', 'mime_type' => 'application/pdf', 'size' => 8]);
        $random = $this->approvedUser();
        $this->actingAs($random)->get("/profile-documents/{$document->id}/download")->assertForbidden();
        $this->actingAs($owner)->get("/profile-documents/{$document->id}/download")->assertOk();
        $admin = $this->admin();
        $this->actingAs($admin)->get("/profile-documents/{$document->id}/download")->assertOk();
    }

    public function test_suspended_user_cannot_send_messages_or_submit_marketplace_actions(): void
    {
        $owner = $this->approvedUser();
        $viewer = $this->approvedUser('distributor');
        $project = $this->publishedProject($owner);
        $viewer->forceFill(['suspended_at' => now()])->save();
        $this->actingAs($viewer)->post("/projects/{$project->slug}/view-requests", ['position' => 'Buyer', 'interest_type' => 'Acquisition', 'reason' => 'Review'])->assertForbidden();
    }

    public function test_user_cannot_favourite_an_unpublished_project_by_guessing_its_id(): void
    {
        $owner = $this->approvedUser();
        $viewer = $this->approvedUser('distributor');
        $project = Project::factory()
            ->for($owner, 'owner')
            ->create([
                'publication_status' => 'draft',
                'published_at' => null,
            ]);

        $this->actingAs($viewer)->post('/saved/toggle', ['type' => 'project', 'id' => $project->id])->assertNotFound();
        $this->assertDatabaseMissing('favourites', ['user_id' => $viewer->id, 'favouritable_id' => $project->id]);
    }

    public function test_non_participant_cannot_report_a_private_message_by_guessing_its_id(): void
    {
        $participant = $this->approvedUser();
        $otherParticipant = $this->approvedUser('distributor');
        $random = $this->approvedUser('investor');
        $conversation = Conversation::create([
            'type' => 'project',
            'created_by' => $participant->id,
        ]);
        $conversation->participants()->attach([$participant->id, $otherParticipant->id]);
        $message = Message::create(['conversation_id' => $conversation->id, 'sender_id' => $participant->id, 'body' => 'Private message']);

        $this->actingAs($random)->post('/reports', [
            'target_type' => 'message',
            'target_id' => $message->id,
            'reason_category' => 'inappropriate_content',
            'description' => 'Attempted report without conversation access.',
        ])->assertNotFound();
        $this->assertDatabaseMissing('reports', ['reportable_id' => $message->id, 'reporter_id' => $random->id]);
    }

    public function test_suspended_access_grant_holder_cannot_download_request_required_document(): void
    {
        Storage::fake('private');
        $owner = $this->approvedUser();
        $viewer = $this->approvedUser('distributor');
        $project = $this->publishedProject($owner);
        Storage::disk('private')->put('project-documents/restricted.pdf', 'restricted');
        $document = ProjectDocument::create(['project_id' => $project->id, 'type' => 'sales_sheet', 'title' => 'Restricted.pdf', 'path' => 'project-documents/restricted.pdf', 'mime_type' => 'application/pdf', 'size' => 10, 'access_level' => 'request_required']);
        AccessGrant::create(['project_id' => $project->id, 'user_id' => $viewer->id, 'granted_by' => $owner->id, 'granted_at' => now()]);
        $viewer->forceFill(['suspended_at' => now()])->save();

        $this->actingAs($viewer)->get("/project-documents/{$document->id}/download")->assertForbidden();
    }
}
