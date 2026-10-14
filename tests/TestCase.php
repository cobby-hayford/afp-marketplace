<?php

namespace Tests;

use App\Models\ContentType;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Notification;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CountrySeeder::class, TaxonomySeeder::class]);
        Notification::fake();
    }

    protected function approvedUser(string $accountType = 'producer', string $role = 'member'): User
    {
        $user = User::factory()->create(['account_type' => $accountType, 'role' => $role, 'email_verified_at' => now()]);
        Profile::factory()->create(['user_id' => $user->id, 'country_id' => $user->country_id, 'status' => 'approved', 'is_public' => true]);

        return $user->fresh('profile');
    }

    protected function admin(string $role = 'administrator'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    protected function projectData(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Test Marketplace Project',
            'alternative_title' => null,
            'logline' => 'A concise commercial pitch for an African content project.',
            'short_synopsis' => 'A short synopsis suitable for the marketplace listing.',
            'description' => 'A complete project description used by the automated test suite.',
            'content_type_id' => ContentType::where('slug', 'feature-film')->value('id'),
            'country_id' => Country::where('iso_code', 'GH')->value('id'),
            'project_stage_id' => ProjectStage::where('slug', 'development')->value('id'),
            'production_year' => 2026,
            'currency' => 'USD',
            'visibility' => 'public',
            'genres' => [],
            'languages' => [],
            'tags' => [],
            'co_production_countries' => [],
        ], $overrides);
    }

    protected function publishedProject(User $owner, array $overrides = []): Project
    {
        return Project::factory()->published()->create(array_replace([
            'owner_id' => $owner->id,
            'visibility' => 'public',
        ], $overrides));
    }
}
