<?php

namespace App\Services\Search;

use Foolz\SphinxQL\Connection;
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

    public function searchPosts(string $query, int $limit, int $offset = 0): array {
        $rows = SphinxQL::create($this->connection)
            ->select('id')
            ->from('posts_idx')
            ->where('is_published', '=', 1)
            ->match(['title', 'body'], $query)
            ->limit($limit, $offset)
            ->execute();

        return array_map(fn ($row) => (int) $row['id'], $rows);
    }
}