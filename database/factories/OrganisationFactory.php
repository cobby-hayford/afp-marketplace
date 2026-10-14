<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return ['created_by' => User::factory(), 'name' => $name, 'slug' => Str::slug($name).'-'.fake()->numberBetween(1, 9999), 'type' => 'Production Company', 'country_id' => Country::first()?->id, 'description' => fake()->paragraph(), 'approval_status' => 'approved', 'is_public' => true];
    }
}
