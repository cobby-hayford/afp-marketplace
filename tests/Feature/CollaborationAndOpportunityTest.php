<?php

namespace Tests\Feature;

use App\Models\CollaborationPost;
use App\Models\CollaborationType;
use App\Models\Country;
use App\Models\OpportunityType;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CollaborationAndOpportunityTest extends TestCase
{
    public function test_collaboration_is_moderated_and_response_opens_conversation(): void
    {
        $author = $this->approvedUser();
        $payload = [
            'title' => 'Seeking Co-Producer for Sahel Documentary',
            'collaboration_type_id' => CollaborationType::where('slug', 'co-producer')->value('id'),
            'country_id' => Country::where('iso_code', 'GH')->value('id'),
            'description' => 'Seeking an experienced co-producer for international financing and distribution.',
            'target_countries' => [],
        ];
        $this->actingAs($author)->post('/collaboration', $payload)->assertRedirect(route('my-collaboration.index'));
        $post = CollaborationPost::where('user_id', $author->id)->firstOrFail();
        $this->post("/collaboration/{$post->slug}/submit")->assertSessionHasNoErrors();
        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/collaboration/{$post->slug}/status", ['status' => 'published'])->assertSessionHasNoErrors();
        $this->get("/collaboration/{$post->slug}")->assertOk();

        $member = $this->approvedUser();
        $this->actingAs($member)->post("/collaboration/{$post->slug}/responses", ['message' => 'Interested in discussing this.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('collaboration_responses', ['collaboration_post_id' => $post->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('conversations', ['type' => 'collaboration', 'context_id' => $post->id]);
    }

    public function test_admin_can_publish_opportunity_and_external_apply_url_is_public(): void
    {
        $admin = $this->admin();
        $type = OpportunityType::firstOrFail();
        $this->actingAs($admin)->post('/admin/opportunities', [
            'opportunity_type_id' => $type->id,
            'title' => 'Pan-African Film Grant',
            'description' => 'A grant opportunity for African screen projects.',
            'external_application_url' => 'https://grants.example.test/apply',
            'publication_status' => 'published',
            'eligible_countries' => [],
            'featured' => false,
        ])->assertSessionHasNoErrors();
        $this->get('/opportunities/pan-african-film-grant')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/opportunities/show')
                ->where(
                    'opportunity.external_application_url',
                    'https://grants.example.test/apply'
                )
            );
    }
}
