<?php

namespace App\Services\Search;

/**
 * Busca full-text por IDs ordenados por relevância. Qualquer falha (queda,
 * timeout, índice vazio) deve resultar em exceção — quem chama aplica o
 * fallback para LIKE (BDR-012: fallback obrigatório).
 */
interface ProductSearchInterface
{
    /**
     * @return array<int> IDs dos produtos, do mais para o menos relevante.
     */
    public function searchIds(string $query, int $limit = 100): array;
}
