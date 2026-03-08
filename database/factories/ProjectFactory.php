<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(rand(2, 3), true);
        $colors = ['#0e8de6', '#059669', '#7c3aed', '#db2777', '#d97706', '#dc2626'];

        return [
            'user_id'     => User::factory(),
            'name'        => ucwords($name),
            'slug'        => Str::slug($name) . '-' . Str::random(6),
            'description' => $this->faker->sentence(10),
            'environment' => $this->faker->randomElement(['test', 'staging']),
            'color'       => $this->faker->randomElement($colors),
            'status'      => 'active',
        ];
    }
}
