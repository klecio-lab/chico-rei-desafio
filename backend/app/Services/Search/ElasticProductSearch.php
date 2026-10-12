<?php

namespace App\Services\Search;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Full-text no Elasticsearch via REST puro (sem SDK: o client oficial exigia
 * downgrade de guzzle/psr-http-message travadas no lock — spike registrado).
 * Nome com peso 3 + stemming PT + fuzziness para typo ("camiseta" acha
 * "camisetas", "caneca" acha "caneca").
 */
class ElasticProductSearch implements ProductSearchInterface
{
    public function searchIds(string $query, int $limit = 100): array
    {
        $response = Http::timeout(config('search.elastic_timeout'))
            ->post(rtrim(config('search.elastic_host'), '/').'/'.config('search.elastic_index').'/_search', [
                'size' => $limit,
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['name^3', 'description', 'category'],
                        'fuzziness' => 'AUTO',
                    ],
                ],
            ]);

        $response->throw();

        $ids = [];

        foreach ($response->json('hits.hits', []) as $hit) {
            if (isset($hit['_id']) && is_numeric($hit['_id'])) {
                $ids[] = (int) $hit['_id'];
            }
        }

        return $ids;
    }

    /**
     * Recria o índice e indexa o catálogo inteiro. Roda manual
     * (`products:reindex`) — sem scheduler, como a importação.
     *
     * @return array{indexed: int}
     */
    public function reindex(): array
    {
        $base = rtrim(config('search.elastic_host'), '/').'/'.config('search.elastic_index');

        // 404 aqui = primeira indexação, segue o jogo.
        $deleted = Http::timeout(config('search.elastic_timeout'))->delete($base);

        if ($deleted->status() !== 404) {
            $deleted->throw();
        }

        Http::timeout(config('search.elastic_timeout'))->put($base, [
            'mappings' => [
                'properties' => [
                    'name' => ['type' => 'text', 'analyzer' => 'portuguese'],
                    'description' => ['type' => 'text', 'analyzer' => 'portuguese'],
                    'category' => ['type' => 'keyword'],
                ],
            ],
        ])->throw();

        $indexed = 0;

        Product::query()->orderBy('id')->chunk(500, function ($products) use ($base, &$indexed): void {
            $bulk = '';

            foreach ($products as $product) {
                $bulk .= json_encode(['index' => ['_id' => $product->id]])."\n";
                $bulk .= json_encode([
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                ])."\n";
            }

            Http::timeout(config('search.elastic_timeout'))
                ->withBody($bulk, 'application/x-ndjson')
                ->post($base.'/_bulk?refresh=true')
                ->throw();

            $indexed += $products->count();
        });

        Log::info('Reindex do catálogo concluído.', ['indexed' => $indexed]);

        return ['indexed' => $indexed];
    }

    public function indexOne(int $id, array $doc): void
    {
        Http::timeout(config('search.elastic_timeout'))
            ->put(rtrim(config('search.elastic_host'), '/').'/'.config('search.elastic_index').'/_doc/'.$id.'?refresh=true', $doc)
            ->throw();
    }

    public function deleteOne(int $id): void
    {
        Http::timeout(config('search.elastic_timeout'))
            ->delete(rtrim(config('search.elastic_host'), '/').'/'.config('search.elastic_index').'/_doc/'.$id.'?refresh=true')
            ->throw();
    }
}
