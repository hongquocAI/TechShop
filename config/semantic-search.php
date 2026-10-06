<?php

return [
    'enabled' => (bool) env('SEMANTIC_SEARCH_ENABLED', false),
    'url' => env('SEMANTIC_SEARCH_URL', 'http://127.0.0.1:8010'),
    'timeout' => 1.2,
    'connect_timeout' => 0.2,
    'limit' => 100,
    'min_score' => (float) env('SEMANTIC_SEARCH_MIN_SCORE', 0.84),
    'directory' => storage_path('app/private/semantic-search'),
];
