<?php

namespace App\Observers;

use App\Jobs\LogProductActivity;
use App\Models\Product;

class ProductObserver
{
    /**
     * O Observer nunca escreve direto: só monta o payload (com o ator do
     * momento) e despacha o Job. A request nunca espera o log (BDR-005).
     */
    public function created(Product $product): void
    {
        LogProductActivity::dispatch(
            $product->id,
            'created',
            auth()->id(),
            ['after' => self::normalizeMoney($product->getAttributes())],
        );
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();
        unset($changes['created_at'], $changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $before = [];
        foreach ($changes as $key => $value) {
            $before[$key] = $product->getOriginal($key);
        }

        LogProductActivity::dispatch(
            $product->id,
            'updated',
            auth()->id(),
            ['before' => self::normalizeMoney($before), 'after' => self::normalizeMoney($changes)],
        );
    }

    public function deleted(Product $product): void
    {
        // Linha órfã de propósito: product_id NULL (a FK não aceitaria o id
        // de um produto já excluído) e o id original sobrevive no snapshot.
        LogProductActivity::dispatch(
            null,
            'deleted',
            auth()->id(),
            ['snapshot' => self::normalizeMoney($product->getAttributes())],
        );
    }

    /**
     * Dinheiro sai sempre string com 2 casas ("49.90") — mesma convenção da
     * API (BDR-003). getAttributes()/getChanges() devolvem o float cru do
     * input (ex.: 49.9), que o json_encode pode expandir para ruído binário.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function normalizeMoney(array $data): array
    {
        if (array_key_exists('price', $data) && $data['price'] !== null) {
            $data['price'] = number_format((float) $data['price'], 2, '.', '');
        }

        return $data;
    }
}
