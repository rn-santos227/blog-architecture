<?php

namespace App\Services\Search;

use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;

class SphinxClient {
    private Connection $connection;
  
    public function __construct()  {
        $this->connection = new Connection();
        $this->connection->setParams([
            'host' => config('sphinx.host'),
            'port' => config('sphinx.port'),
        ]);
    }

    public function searchPosts(
        string $query,
        int $limit,
        int $offset = 0,
        ?int $authorId = null,
        ?int $fromTs = null,
        ?int $toTs = null
    ): array {
        $sphinx = SphinxQL::create($this->connection)
            ->select('id')
            ->from('posts_idx_shard_0, posts_idx_shard_1')
            ->where('is_published', '=', 1)
            ->match(['title', 'body'], $query)
            ->limit($limit, $offset);

        if ($authorId !== null) {
            $sphinx->where('user_id', '=', $authorId);
        }

        if ($fromTs !== null) {
            $sphinx->where('published_at_ts', '>=', $fromTs);
        }

        if ($toTs !== null) {
            $sphinx->where('published_at_ts', '<=', $toTs);
        }

        return array_map(
            fn ($row) => (int) $row['id'],
            $sphinx->execute()
        );
    }
}