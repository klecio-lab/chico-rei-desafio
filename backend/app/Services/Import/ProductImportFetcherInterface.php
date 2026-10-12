<?php

namespace App\Services\Import;

/**
 * Busca itens brutos do catálogo externo para importação.
 */
interface ProductImportFetcherInterface
{
    /**
     * @return array<int, array<string, mixed>> Itens crus (formato do provedor).
     */
    public function fetchTema(string $tema, ?int $limit = null): array;
}
