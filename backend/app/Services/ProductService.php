<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        return DB::transaction(fn () => Product::create($this->withUpload($data)));
    }

    /**
     * @param  array<string, mixed>  $data  Dado já validado do UpdateProductRequest.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $oldImage = $product->image;
            $product->update($this->withUpload($data));
            $this->deleteUpload($oldImage, $product->image);

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $image = $product->image;
            $product->delete();
            $this->deleteUpload($image, null);
        });
    }

    /**
     * Se image_file veio, salva no disco public e usa a URL pública absoluta
     * (o front está em outra origem — relativa quebraria). File vence URL.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withUpload(array $data): array
    {
        $file = $data['image_file'] ?? null;
        unset($data['image_file']);

        if ($file instanceof UploadedFile) {
            $disk = Storage::disk('public');
            $data['image'] = url($disk->url($disk->putFile('products', $file)));
        }

        return $data;
    }

    /**
     * Apaga o arquivo antigo SOMENTE se era upload nosso (URL local em
     * /storage/products). URLs externas e placeholders jamais são tocados.
     */
    private function deleteUpload(?string $oldImage, ?string $newImage): void
    {
        if ($oldImage === null || $oldImage === $newImage) {
            return;
        }

        $prefix = rtrim(config('app.url'), '/').'/storage/products/';

        if (str_starts_with($oldImage, $prefix)) {
            Storage::disk('public')->delete('products/'.basename($oldImage));
        }
    }
}
