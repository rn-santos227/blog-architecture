<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Post::class;

    public function definition(): array
    {
        $isPublished = $this->faker->boolean(80);
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'title' => $this->faker->sentence(6),
            'body' => $this->faker->paragraphs(5, true),
            'status' => $isPublished ? 'published' : 'draft',
            'published_at' => $isPublished
                ? now()->subDays(rand(0, 365))
                : null,
        ];
    }
}
