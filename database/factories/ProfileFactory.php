<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $country = Country::first();

        return ['user_id' => User::factory(), 'slug' => Str::slug(fake()->unique()->name()), 'professional_name' => fake()->name(), 'country_id' => $country?->id, 'short_bio' => fake()->sentence(), 'biography' => fake()->paragraphs(2, true), 'status' => 'approved', 'is_public' => true, 'approved_at' => now()];
    }
}
