<?php

return [
    'host' => env('SPHINX_HOST', '127.0.0.1'),
    'port' => env('SPHINX_PORT', 9306),
    'indexer_path' => env('SPHINX_INDEXER_PATH', 'indexer'),
    'config_path' => env('SPHINX_CONFIG_PATH', '/etc/manticoresearch/manticore.conf'),
];