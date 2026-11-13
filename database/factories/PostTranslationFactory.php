<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostTranslation>
 */
class PostTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'locale' => 'en',
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'body' => fake()->paragraphs(3, true),
        ];
    }
}
