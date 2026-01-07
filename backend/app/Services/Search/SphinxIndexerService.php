<?php

namespace App\Services\Search;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use RuntimeException;

class SphinxIndexerService
{
    public function reindexPosts(): void
    {
        $this->reindexIndex('posts_idx');
    }

    public function reindexIndex(string $indexName): void
    {
        $indexerPath = config('sphinx.indexer_path', 'indexer');
        $configPath = config('sphinx.config_path', '/etc/manticoresearch/manticore.conf');
        $command = [
            $indexerPath,
            $indexName,
            '--rotate',
        ];

        if (! empty($configPath)) {
            if (file_exists($configPath)) {
                $command[] = '--config';
                $command[] = $configPath;
            } else {
                Log::warning('Sphinx config file not found; running indexer without --config.', [
                    'config_path' => $configPath,
                    'index' => $indexName,
                ]);
            }
        }

        $process = new Process($command);
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Sphinx reindex failed: ' . $process->getErrorOutput()
            );
        }
    }
}
