<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $country = Country::firstOrCreate(['iso_code' => 'GH'], ['name' => 'Ghana', 'slug' => 'ghana', 'region' => 'Western Africa', 'is_african' => true]);

        return ['first_name' => fake()->firstName(), 'last_name' => fake()->lastName(), 'email' => fake()->unique()->safeEmail(), 'email_verified_at' => now(), 'password' => Hash::make('Password1234'), 'country_id' => $country->id, 'role' => 'member', 'account_type' => 'producer'];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'administrator']);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['suspended_at' => now()]);
    }
}
