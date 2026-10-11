<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexProductRequest;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(IndexProductRequest $request): JsonResponse
    {
        $paginator = $this->products->paginate($request->validated());

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'image' => $product->image,
                'price' => $product->price,
                'category' => $product->category,
                'stock' => $product->stock,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                // Últimas 10 movimentações de product_logs (Observer + Job, BDR-005).
                'logs' => $product->logs()->latest()->limit(10)->get(),
            ],
        ], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return response()->json([
            'message' => 'Produto criado com sucesso.',
            'data' => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->products->update(Product::findOrFail($id), $request->validated());

        return response()->json([
            'message' => 'Produto atualizado com sucesso.',
            'data' => $product,
        ], 200);
    }

    public function destroy(int $id): Response
    {
        $this->products->delete(Product::findOrFail($id));

        return response()->noContent();
    }
}
