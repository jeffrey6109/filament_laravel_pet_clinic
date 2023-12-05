<?php

namespace Database\Factories;

use App\Enums\PetSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'date_of_birth' => fake()->date(),
            'species' => PetSpecies::Geese,
            'type' => 'Eastern Bearded Dragon',
            'avatar' => fake()->image(),
        ];
    }
}
