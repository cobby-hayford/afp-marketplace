<?php

namespace Tests\Feature;

use App\Models\BuyingRequest;
use App\Models\ContentType;
use Tests\TestCase;

class BuyingRequestTest extends TestCase
{
    private function payload(): array
    {
        return [
            'title' => 'West African Drama Series Wanted',
            'description' => 'Seeking premium drama series for regional broadcast acquisition.',
            'content_types' => [ContentType::where('slug', 'tv-series')->value('id')],
            'genres' => [], 'countries' => [], 'languages' => [],
        ];
    }

    public function test_authorized_buyer_can_create_submit_and_admin_publish_request(): void
    {
        $buyer = $this->approvedUser('broadcaster');
        $this->actingAs($buyer)->post('/buying-requests', $this->payload())->assertRedirect(route('my-buying-requests.index'));
        $request = BuyingRequest::where('user_id', $buyer->id)->firstOrFail();
        $this->assertSame('draft', $request->publication_status);
        $this->post("/buying-requests/{$request->slug}/submit")->assertSessionHasNoErrors();
        $this->assertSame('submitted', $request->fresh()->publication_status);

        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/buying-requests/{$request->slug}/status", ['status' => 'published'])->assertSessionHasNoErrors();
        $this->get("/buying-requests/{$request->slug}")->assertOk();
    }

    public function test_producer_cannot_publish_buying_request(): void
    {
        $producer = $this->approvedUser('producer');
        $this->actingAs($producer)->post('/buying-requests', $this->payload())->assertForbidden();
    }

    public function test_producer_can_respond_only_with_owned_published_project(): void
    {
        $buyer = $this->approvedUser('broadcaster');
        $request = BuyingRequest::factory()->published()->create(['user_id' => $buyer->id]);
        $producer = $this->approvedUser('producer');
        $own = $this->publishedProject($producer);
        $otherOwner = $this->approvedUser('producer');
        $other = $this->publishedProject($otherOwner);

        $this->actingAs($producer)->post("/buying-requests/{$request->slug}/responses", ['project_id' => $own->id, 'message' => 'Please review this title.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('buying_request_responses', ['buying_request_id' => $request->id, 'project_id' => $own->id]);
        $this->post("/buying-requests/{$request->slug}/responses", ['project_id' => $other->id])->assertForbidden();
    }
}
