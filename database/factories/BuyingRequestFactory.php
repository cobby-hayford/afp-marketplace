<?php

namespace Database\Factories;

use App\Models\BuyingRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BuyingRequestFactory extends Factory
{
    protected $model = BuyingRequest::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return ['user_id' => User::factory(), 'title' => $title, 'slug' => Str::slug($title).'-'.fake()->numberBetween(1, 99999), 'description' => fake()->paragraph(), 'publication_status' => 'draft'];
    }

    public function published(): static
    {
        return $this->state(fn () => ['publication_status' => 'published', 'published_at' => now()]);
    }
}
