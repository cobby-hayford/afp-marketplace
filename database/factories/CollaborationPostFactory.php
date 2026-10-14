<?php

namespace Database\Factories;

use App\Models\CollaborationPost;
use App\Models\CollaborationType;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CollaborationPostFactory extends Factory
{
    protected $model = CollaborationPost::class;

    public function definition(): array
    {
        $type = CollaborationType::firstOrCreate(['name' => 'Co-Producer'], ['slug' => 'co-producer']);
        $title = fake()->sentence(4);

        return ['user_id' => User::factory(), 'collaboration_type_id' => $type->id, 'country_id' => Country::first()?->id, 'title' => $title, 'slug' => Str::slug($title).'-'.fake()->numberBetween(1, 99999), 'description' => fake()->paragraph(), 'publication_status' => 'draft'];
    }
}
