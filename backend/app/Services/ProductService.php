<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
