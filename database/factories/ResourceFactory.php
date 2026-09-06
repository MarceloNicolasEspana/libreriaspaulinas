<?php

namespace Database\Factories;

use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\Resource> */
class ResourceFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'active' => true, 'published_at' => now()->subDay(),
            'description' => fake()->paragraph(), 'resource_category_id' => ResourceCategory::factory(), 'audience' => 'Catequistas y comunidades',

        ];
    }
}
