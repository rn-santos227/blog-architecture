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
        $shard = $this->shardRouter->shardForUser($userId);

        return DB::connection($connection)->transaction(function () use ($data, $userId, $indexName, $shard) {
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

    public function cursorPaginateByUser(int $userId, int $perPage = 10, ?string $cursor = null) {
        $connection = $this->shardRouter->connectionForUser($userId);
        return Post::on($connection)
            ->where('user_id', $userId)
            ->with(['user:id,name', 'tags:id,name,slug'])
            ->orderByDesc('id')
            ->cursorPaginate(
                perPage: $perPage,
                cursor: $cursor
            );
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

    public function search(
        ?string $query,
        int $limit = 10,
        int $page = 1,
        ?int $authorId = null,
        ?array $tags = null,
        ?string $from = null,
        ?string $to = null
    ): Collection {
        $cacheStore = Cache::store('redis');
        $limit = max(1, min($limit, 50));
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $query = trim((string) $query);
        $tags = collect($tags ?? [])
            ->filter()
            ->map(fn (string $tag) => str($tag)->slug()->toString())
            ->values()
            ->all();

        $version = $cacheStore->get('posts:search:version', 1);

        $cacheKey = sprintf(
            'posts:search:v%d:%s:%d:%d:%s:%s:%s:%s',
            $version,
            md5(mb_strtolower($query)),
            $limit,
            $page,
            $authorId ?? 'any',
            empty($tags) ? 'any' : implode(',', $tags),
            $from ?? 'any',
            $to ?? 'any'
        );

        $results = $cacheStore->remember($cacheKey, now()->addMinutes(5), function () use (
            $query,
            $limit,
            $offset,
            $authorId,
            $tags,
            $from,
            $to
        ) {
            if ($query === '') {
                $posts = Post::query()
                    ->where('status', 'published')
                    ->with(['user:id,name', 'tags:id,name,slug']);

                if ($authorId !== null) {
                    $posts->where('user_id', $authorId);
                }

                if ($from !== null) {
                    $posts->where('published_at', '>=', $from);
                }

                if ($to !== null) {
                    $posts->where('published_at', '<=', $to);
                }

                foreach ($tags as $tagSlug) {
                    $posts->whereHas('tags', function ($query) use ($tagSlug) {
                        $query->where('tags.slug', $tagSlug);
                    });
                }

                return $posts
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
            }

            $ids = $this->sphinx->searchPosts(
                query: $query,
                limit: $limit,
                offset: $offset,
                authorId: $authorId,
                fromTs: $from ? strtotime($from) : null,
                toTs: $to ? strtotime($to) : null
            );

            if (empty($ids)) {
                return new Collection();
            }

            $posts = Post::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->with(['user:id,name', 'tags:id,name,slug']);

            foreach ($tags as $tagSlug) {
                $posts->whereHas('tags', function ($query) use ($tagSlug) {
                    $query->where('tags.slug', $tagSlug);
                });
            }

            return $posts
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                ->get();
        });

        return $results instanceof Collection
            ? $results
            : new Collection(is_array($results) ? $results : $results->all());
    }

    public function searchByUser(int $userId, string $query, int $limit = 10): Collection {
        $connection = $this->shardRouter->connectionForUser($userId);
        $limit = max(1, min($limit, 50));
        $query = trim($query);

        $version = $cacheStore->get('posts:search:version', 1);
        $cacheKey = sprintf(
            'posts:search:user:v%d:%d:%s:%d',
            $version,
            $userId,
            md5(mb_strtolower($query)),
            $limit
        );

        $results = $cacheStore->remember($cacheKey, now()->addMinutes(5), function () use (
            $connection,
            $userId,
            $query,
            $limit
        ) {
            return Post::on($connection)
                ->where('user_id', $userId)
                ->when($query !== '', function ($builder) use ($query) {
                    $builder->where(function ($queryBuilder) use ($query) {
                        $queryBuilder
                            ->where('title', 'like', '%' . $query . '%')
                            ->orWhere('body', 'like', '%' . $query . '%');
                    });
                })
                ->with(['user:id,name', 'tags:id,name,slug'])
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });

        return $results instanceof Collection
            ? $results
            : new Collection(is_array($results) ? $results : $results->all());
    }

    private function invalidateSearchCache(): void {
        Cache::store('redis')->increment('posts:search:version');
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
