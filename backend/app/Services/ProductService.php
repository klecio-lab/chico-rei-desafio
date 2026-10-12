<?php

namespace App\Services;

use App\Models\Product;
use App\Services\Search\ElasticProductSearch;
use App\Services\Search\ProductSearchInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(private readonly ProductSearchInterface $search) {}

    /**
     * Listagem pública com filtros + ordenação + paginação.
     *
     * @param  array<string, mixed>  $filters  Dado já validado do IndexProductRequest.
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Product::query();

        if (! empty($filters['q'])) {
            $ids = $this->searchIds($filters['q']);

            if ($ids !== null) {
                if ($ids === []) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('id', $ids);

                    // FIELD() preserva a ordem de relevância do ES — salvo
                    // sort explícito, que mantém precedência do usuário.
                    if (! isset($filters['sort'])) {
                        $query->orderByRaw('FIELD(id, '.implode(',', array_map(intval(...), $ids)).')');
                    }
                }
            } else {
                // Fallback obrigatório: LIKE com índice (BDR-012).
                // Collation padrão do MySQL (utf8mb4_unicode_ci) => case-insensitive.
                $query->where('name', 'like', '%'.$filters['q'].'%');
            }
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
     * IDs via ES quando habilitado; null = usar LIKE (desligado ou falha).
     *
     * @return array<int>|null
     */
    private function searchIds(string $query): ?array
    {
        if (! config('search.elastic_enabled')) {
            return null;
        }

        try {
            return $this->search->searchIds($query);
        } catch (\Throwable $e) {
            Log::warning('Busca ES falhou; fallback para LIKE.', [
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);

            return null;
        }
    }

    /**
     * Sincroniza o índice no CUD — best-effort fora da transaction:
     * nunca quebra a escrita.
     *
     * @param  array<string, mixed>  $data  Dado já validado do StoreProductRequest.
     */
    public function create(array $data): Product
    {
        $product = DB::transaction(fn () => Product::create($this->withUpload($data)));
        $this->syncIndex($product);

        return $product;
    }

    /**
     * @param  array<string, mixed>  $data  Dado já validado do UpdateProductRequest.
     */
    public function update(Product $product, array $data): Product
    {
        $product = DB::transaction(function () use ($product, $data): Product {
            $oldImage = $product->image;
            $product->update($this->withUpload($data));
            $this->deleteUpload($oldImage, $product->image);

            return $product->refresh();
        });
        $this->syncIndex($product);

        return $product;
    }

    public function delete(Product $product): void
    {
        $id = $product->id;

        DB::transaction(function () use ($product): void {
            $image = $product->image;
            $product->delete();
            $this->deleteUpload($image, null);
        });
        $this->removeFromIndex($id);
    }

    private function syncIndex(Product $product): void
    {
        if (! config('search.elastic_enabled')) {
            return;
        }

        try {
            app(ElasticProductSearch::class)->indexOne($product->id, [
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Indexação ES falhou; escrita preservada.', [
                'product_id' => $product->id,
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }
    }

    private function removeFromIndex(int $id): void
    {
        if (! config('search.elastic_enabled')) {
            return;
        }

        try {
            app(ElasticProductSearch::class)->deleteOne($id);
        } catch (\Throwable $e) {
            Log::warning('Remoção do índice ES falhou; exclusão preservada.', [
                'product_id' => $id,
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }
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
