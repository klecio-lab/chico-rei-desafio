<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Http;

/**
 * Lê a listagem de um tema no site da Chico Rei (1 request) e devolve os
 * produtos do JSON embutido no HTML. Nada aqui persiste — quem decide o que
 * entra no banco é o comando via upsert (BDR-009).
 */
class ChicoReiFetcher implements ProductImportFetcherInterface
{
    public function fetchTema(string $tema, ?int $limit = null): array
    {
        $url = rtrim(config('import.base_url'), '/')."/camiseta/{$tema}/";

        $response = Http::withUserAgent(config('import.user_agent'))
            ->timeout(config('import.timeout_seconds'))
            ->get($url, ['per_page' => $limit ?? config('import.default_limit')]);

        $response->throw();

        $throttleMs = (int) config('import.throttle_ms');

        if ($throttleMs > 0) {
            usleep($throttleMs * 1000);
        }

        return ChicoReiParser::parseListing($response->body(), config('import.base_url'));
    }
}
