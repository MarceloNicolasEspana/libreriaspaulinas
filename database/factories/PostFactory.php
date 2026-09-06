<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'active' => true, 'published_at' => now()->subDay(),

            'excerpt' => fake()->paragraph(), 'content' => fake()->paragraphs(4, true),
        ];
    }
}
