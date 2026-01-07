<?php

namespace App\Services\Tag;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    public function listWithPostCounts(int $limit = 50, ?string $query = null): Collection {
        $builder = Tag::query()
            ->withCount([
                'posts as posts_count' => function ($query) {
                    $query->where('status', 'published');
                },
            ])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->orderBy('name');

        if ($query) {
            $search = '%' . $query . '%';
            $builder->where(function ($queryBuilder) use ($search) {
                $queryBuilder
                    ->where('name', 'like', $search)
                    ->orWhere('slug', 'like', $search);
            });
        }

        return $builder
            ->limit($limit)
            ->get(['id', 'name', 'slug']);
    }
}
