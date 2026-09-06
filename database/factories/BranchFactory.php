<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Paulinas '.$this->faker->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 99999),
            'address' => $this->faker->streetAddress(),
            'commune' => $this->faker->city(),
            'region' => 'Región Metropolitana de Santiago',
            'phone' => '+56 2 '.$this->faker->numerify('#### ####'),
            'whatsapp' => '+56 9 '.$this->faker->numerify('#### ####'),
            'email' => $this->faker->unique()->safeEmail(),
            'opening_hours' => [
                ['days' => 'Lunes a viernes', 'periods' => ['09:00–18:00']],
                ['days' => 'Sábados', 'periods' => ['10:00–13:00']],
            ],
            'latitude' => $this->faker->latitude(-56, -17),
            'longitude' => $this->faker->longitude(-76, -66),
            'active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
