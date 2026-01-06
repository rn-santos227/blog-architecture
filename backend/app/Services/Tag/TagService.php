<?php

namespace App\Services\Tag;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    public function listWithPostCounts(int $limit = 50): Collection
    {
        return Tag::query()
            ->withCount([
                'posts as posts_count' => function ($query) {
                    $query->where('status', 'published');
                },
            ])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);
    }
}
