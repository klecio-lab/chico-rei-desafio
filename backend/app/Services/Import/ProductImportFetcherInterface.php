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

    /**
     * Mesma coisa a partir de uma URL de listagem explícita (ex.: base /camiseta/).
     *
     * @return array<int, array<string, mixed>> Itens crus (formato do provedor).
     */
    public function fetchUrl(string $url, ?int $limit = null): array;
}
