<?php

namespace App\Services\Search;

use Symfony\Component\Process\Process;
use RuntimeException;

class SphinxIndexerService
{
    public function reindexPosts(): void
    {
        $process = new Process([
            'indexer',
            'posts_idx',
            '--rotate',
            '--config',
            '/etc/manticoresearch/manticore.conf',
        ]);

        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Sphinx reindex failed: ' . $process->getErrorOutput()
            );
        }
    }
}
