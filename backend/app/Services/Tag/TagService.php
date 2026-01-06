<?php

namespace App\Services\Tag;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    public function listWithPostCounts(int $minPosts = 0, int $limit = 50): Collection
    {
        return Tag::query()
            ->withCount([
                'posts as posts_count' => function ($query) {
                    $query->where('status', 'published');
                },
            ])
            ->when($minPosts > 0, function ($query) use ($minPosts) {
                $query->having('posts_count', '>=', $minPosts);
            })
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);
    }
}
