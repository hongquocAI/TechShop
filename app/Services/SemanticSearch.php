<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemanticSearch
{
    /** null means unavailable: the controller keeps its keyword search. */
    public function search(string $query, array $filters): ?array
    {
        if (! config('semantic-search.enabled') || Cache::has('semantic-search:unavailable')) {
            return null;
        }

        try {
            $response = Http::connectTimeout(config('semantic-search.connect_timeout'))
                ->timeout(config('semantic-search.timeout'))
                ->post(rtrim(config('semantic-search.url'), '/').'/search', [
                    'query' => mb_substr($query, 0, 300),
                    'filters' => $filters,
                    'limit' => config('semantic-search.limit'),
                    'min_score' => config('semantic-search.min_score'),
                ]);

            $ids = $response->json('ids');
            if (! $response->successful() || ! is_array($ids)
                || count($ids) > config('semantic-search.limit')
                || collect($ids)->contains(fn ($id) => ! is_int($id) || $id < 1)) {
                throw new \RuntimeException('Semantic search response unavailable or invalid.');
            }

            return array_values(array_unique($ids));
        } catch (\Throwable $exception) {
            // Brief circuit breaker prevents repeated waits when the local service is stopped.
            Cache::put('semantic-search:unavailable', true, 15);
            Log::notice('Semantic search unavailable; using keywords.', ['reason' => $exception->getMessage()]);

            return null;
        }
    }
}
