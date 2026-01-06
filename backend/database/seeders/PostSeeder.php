<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tagIds = Tag::pluck('id')->all();
        Post::factory()
            ->count(10_000)
            ->create()
            ->each(function (Post $post) use ($tagIds) {
                $post->tags()->attach(
                    collect($tagIds)->random(rand(1, 5))->all()
                );
            });
    }
}
