<?php

namespace Database\Factories;

use App\Models\ContentType;
use App\Models\Country;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $country = Country::firstOrCreate(['iso_code' => 'GH'], ['name' => 'Ghana', 'slug' => 'ghana', 'region' => 'Western Africa', 'is_african' => true]);
        $type = ContentType::firstOrCreate(['name' => 'Feature Film'], ['slug' => 'feature-film']);
        $stage = ProjectStage::firstOrCreate(['name' => 'Development'], ['slug' => 'development', 'sort_order' => 2]);
        $title = fake()->unique()->sentence(3);

        return ['owner_id' => User::factory(), 'title' => $title, 'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 99999), 'logline' => fake()->sentence(), 'short_synopsis' => fake()->paragraph(), 'description' => fake()->paragraphs(3, true), 'content_type_id' => $type->id, 'country_id' => $country->id, 'project_stage_id' => $stage->id, 'publication_status' => 'draft'];
    }

    public function published(): static
    {
        return $this->state(fn () => ['publication_status' => 'published', 'published_at' => now()]);
    }
}
