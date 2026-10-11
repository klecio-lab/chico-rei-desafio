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
            ['after' => $product->getAttributes()],
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
            ['before' => $before, 'after' => $changes],
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
            ['snapshot' => $product->getAttributes()],
        );
    }
}
