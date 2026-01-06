<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Tag;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostRepository {
    public function create(array $data, int $userId): Post {
        return DB::transaction(function () use ($data, $userId) {
            $post = Post::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'body' => $data['body'],
                'status' => $data['status'] ?? 'draft',
                'published_at' => $data['status'] === 'published'
                    ? now()
                    : null,
            ]);

            if (!empty($data['tags'])) {
                $tagIds = $this->resolveTagIds($data['tags']);
                $post->tags()->sync($tagIds);
            }

            $this->invalidateSearchCache();
            return $post;
        });
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $post->update([
                'title' => $data['title'] ?? $post->title,
                'body' => $data['body'] ?? $post->body,
                'status' => $data['status'] ?? $post->status,
                'published_at' => $data['status'] === 'published'
                    ? ($post->published_at ?? now())
                    : null,
            ]);

            if (array_key_exists('tags', $data)) {
                $tagIds = empty($data['tags'])
                    ? []
                    : $this->resolveTagIds($data['tags']);

                $post->tags()->sync($tagIds);
            }

            $this->invalidateSearchCache();
            return $post;
        });
    }

    public function delete(Post $post): void {
        DB::transaction(function () use ($post) {
            $post->delete();

            $this->invalidateSearchCache();
        });
    }

    public function restore(Post $post): void {
        DB::transaction(function () use ($post) {
            $post->restore();

            $this->invalidateSearchCache();
        });
    }

    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator {
        return Post::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Post {
        return Post::with('tags')->find($id);
    }

    private function invalidateSearchCache(): void {
        Cache::tags(['posts', 'search'])->flush();
    }

    private function resolveTagIds(array $tags): array {
        return collect($tags)
            ->map(fn (string $tag) => trim(mb_strtolower($tag)))
            ->filter()
            ->unique()
            ->map(function (string $name) {
                return Tag::firstOrCreate(
                    ['name' => $name],
                    ['slug' => str($name)->slug()]
                )->id;
            })
            ->values()
            ->all();
    }
}
