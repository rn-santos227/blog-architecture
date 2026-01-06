<?php

namespace App\Services\Sharding;

class PostShardRouter
{
    public function shardForUser(int $userId): int {
        return $userId % 2;
    }

    public function connectionForUser(int $userId): string {
        return 'mysql_shard_' . $this->shardForUser($userId);
    }

    public function connectionForPostId(int $postId): ?string {
        $lookup = \App\Models\PostLookup::find($postId);

        if (!$lookup) {
            return null;
        }

        return 'mysql_shard_' . $lookup->shard;
    }

    public function indexForUser(int $userId): string {
        return 'posts_idx_shard_' . $this->shardForUser($userId);
    }
}
