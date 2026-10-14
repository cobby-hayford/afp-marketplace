<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Project;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    public function test_search_and_country_filter_return_only_matching_published_public_projects(): void
    {
        $owner = $this->approvedUser();
        $ghana = Country::where('iso_code', 'GH')->firstOrFail();
        $kenya = Country::where('iso_code', 'KE')->firstOrFail();
        $match = $this->publishedProject($owner, ['title' => 'Savannah Echo', 'country_id' => $ghana->id]);
        $this->publishedProject($owner, ['title' => 'Coastal Light', 'country_id' => $kenya->id]);
        Project::factory()->create(['owner_id' => $owner->id, 'title' => 'Savannah Draft', 'country_id' => $ghana->id]);

        $this->get('/projects?q=Savannah&country_id='.$ghana->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('public/projects/index')->where('projects.total', 1)->where('projects.data.0.id', $match->id));
    }

    public function test_members_only_project_is_absent_from_public_index_search_and_sitemap(): void
    {
        $owner = $this->approvedUser();
        $project = $this->publishedProject($owner, ['title' => 'Private Industry Cut', 'visibility' => 'members']);
        $this->get('/projects')->assertDontSee('Private Industry Cut');
        $this->get('/search?q=Private')->assertDontSee('Private Industry Cut');
        $this->get('/sitemap.xml')->assertDontSee($project->slug);
        $this->get("/projects/{$project->slug}")->assertNotFound();
        $viewer = $this->approvedUser('distributor');
        $this->actingAs($viewer)->get("/projects/{$project->slug}")->assertOk();
    }

    public function test_filters_include_availability(): void
    {
        $owner = $this->approvedUser();
        $available = $this->publishedProject($owner, ['title' => 'Rights Ready', 'rights_available' => 'Worldwide broadcast rights']);
        $this->publishedProject($owner, ['title' => 'No Rights Yet', 'rights_available' => null]);
        $this->get('/projects?availability=rights_listed')
            ->assertInertia(fn (Assert $page) => $page->where('projects.total', 1)->where('projects.data.0.id', $available->id));
    }
}
