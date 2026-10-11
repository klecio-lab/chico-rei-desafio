<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Listagem pública com filtros + ordenação + paginação.
     *
     * @param  array<string, mixed>  $filters  Dado já validado do IndexProductRequest.
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Product::query();

        if (! empty($filters['q'])) {
            // Collation padrão do MySQL (utf8mb4_unicode_ci) => LIKE case-insensitive.
            $query->where('name', 'like', '%'.$filters['q'].'%');
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['in_stock']) && $filters['in_stock'] !== null) {
            // stock é unsigned: true => > 0, false => zerado.
            $query->when(
                (bool) $filters['in_stock'],
                fn ($query) => $query->where('stock', '>', 0),
                fn ($query) => $query->where('stock', '=', 0),
            );
        }

        $sort = $filters['sort'] ?? '-created_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';

        $query->orderBy(ltrim($sort, '-'), $direction);

        return $query->paginate($filters['per_page'] ?? config('api.pagination_default'));
    }

    /**
     * @param  array<string, mixed>  $data  Dado já validado do StoreProductRequest.
     */
    public function create(array $data): Product
    {
        return DB::transaction(fn () => Product::create($data));
    }

    /**
     * @param  array<string, mixed>  $data  Dado já validado do UpdateProductRequest.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->update($data);

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(fn () => $product->delete());
    }
}
