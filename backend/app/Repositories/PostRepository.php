<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Tag;
use App\Services\Search\SphinxClient;
use App\Services\Sharding\PostShardRouter;
use App\Jobs\Search\ReindexPostsShardJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostRepository {
    public function __construct(
        private readonly SphinxClient $sphinx,
        private readonly PostShardRouter $shardRouter
    ) {}

    public function create(array $data, int $userId): Post {
        $connection = $this->shardRouter->connectionForUser($userId);
        $indexName = $this->shardRouter->indexForUser($userId);

        return DB::connection($connection)->transaction(function () use ($data, $userId, $indexName) {
            $post = Post::on(DB::getDefaultConnection())->create([
                'user_id' => $userId,
                'title' => $data['title'],
                'body' => $data['body'],
                'status' => $data['status'] ?? 'draft',
                'published_at' => ($data['status'] ?? 'draft') === 'published'
                    ? now()
                    : null,
            ]);

            \App\Models\PostLookup::create([
                'post_id' => $post->id,
                'shard' => $shard,
                'user_id' => $userId,
            ]);

            if (!empty($data['tags'])) {
                $post->tags()->sync(
                    $this->resolveTagIds($data['tags'])
                );
            }

            $this->invalidateSearchCache();

            ReindexPostsShardJob::dispatch($indexName)
                ->delay(now()->addSeconds(5));

            return $post;
        });
    }


    public function update(Post $post, array $data): Post {
        $userId = $post->user_id;
        $connection = $this->shardRouter->connectionForUser($userId);
        $indexName = $this->shardRouter->indexForUser($userId);

        return DB::connection($connection)->transaction(function () use ($post, $data, $indexName) {
            $post->update([
                'title' => $data['title'] ?? $post->title,
                'body' => $data['body'] ?? $post->body,
                'status' => $data['status'] ?? $post->status,
                'published_at' => ($data['status'] ?? $post->status) === 'published'
                    ? ($post->published_at ?? now())
                    : null,
            ]);

            if (array_key_exists('tags', $data)) {
                $post->tags()->sync(
                    empty($data['tags'])
                        ? []
                        : $this->resolveTagIds($data['tags'])
                );
            }

            $this->invalidateSearchCache();

            ReindexPostsShardJob::dispatch($indexName)
                ->delay(now()->addSeconds(5));

            return $post;
        });
    }

    public function delete(Post $post): void {
        $userId = $post->user_id;
        $connection = $this->shardRouter->connectionForUser($userId);
        $indexName = $this->shardRouter->indexForUser($userId);

        DB::connection($connection)->transaction(function () use ($post, $indexName) {
            $post->delete();

            $this->invalidateSearchCache();

            ReindexPostsShardJob::dispatch($indexName)
                ->delay(now()->addSeconds(5));
        });
    }

    public function restore(Post $post): void {
        $userId = $post->user_id;
        $connection = $this->shardRouter->connectionForUser($userId);
        $indexName = $this->shardRouter->indexForUser($userId);

        DB::connection($connection)->transaction(function () use ($post, $indexName) {
            $post->restore();

            $this->invalidateSearchCache();

            ReindexPostsShardJob::dispatch($indexName)
                ->delay(now()->addSeconds(5));
        });
    }

    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator {
        $connection = $this->shardRouter->connectionForUser($userId);
        return Post::on($connection)
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Post {
        $connection = $this->shardRouter->connectionForPostId($id);
        if (!$connection) {
            return null;
        }

        return Post::on($connection)
            ->with(['tags', 'user'])
            ->find($id);
    }

    public function search(string $query, int $limit = 10, int $page = 1): Collection {
        $limit = max(1, min($limit, 50));
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $version = Cache::get('posts:search:version', 1);

        $cacheKey = sprintf(
            'posts:search:v%d:%s:%d:%d',
            $version,
            md5(mb_strtolower(trim($query))),
            $limit,
            $page
        );

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query, $limit, $offset) {
            $ids = $this->sphinx->searchPosts($query, $limit, $offset);

            if (empty($ids)) {
                return collect();
            }

            return Post::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->with(['user:id,name', 'tags:id,name,slug'])
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                ->get();
        });
    }

    private function invalidateSearchCache(): void {
        Cache::increment('posts:search:version');
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
