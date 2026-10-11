<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_produto_gera_log(): void
    {
        $user = $this->authUser();

        $id = $this->postJson('/api/products', [
            'name' => 'Camiseta Logada',
            'price' => 59.90,
            'category' => 'camisetas',
            'stock' => 10,
        ])->assertStatus(201)->json('data.id');

        $log = ProductLog::where('product_id', $id)->sole();

        $this->assertSame('created', $log->action);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('Camiseta Logada', $log->changes['after']['name']);
    }

    public function test_atualizar_produto_gera_log_com_diff(): void
    {
        $user = $this->authUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 10]);

        $this->putJson("/api/products/{$product->id}", [
            'name' => $product->name,
            'description' => $product->description,
            'image' => $product->image,
            'price' => 75,
            'category' => $product->category,
            'stock' => 10,
        ])->assertStatus(200);

        $log = ProductLog::where('product_id', $product->id)->where('action', 'updated')->sole();

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('50.00', (string) $log->changes['before']['price']);
        $this->assertSame('75.00', $log->changes['after']['price']);
        $this->assertArrayNotHasKey('stock', $log->changes['after']);
    }

    public function test_log_grava_price_sempre_com_duas_casas(): void
    {
        $this->authUser();

        // 49.9 em float contamina o payload cru (ruído binário no JSON) —
        // regressão do ruído visto no E2E ("49.900001525878906").
        $id = $this->postJson('/api/products', [
            'name' => 'Camiseta Preço',
            'price' => 49.9,
            'category' => 'camisetas',
            'stock' => 10,
        ])->assertStatus(201)->json('data.id');

        $this->assertSame(
            '49.90',
            ProductLog::where('product_id', $id)->where('action', 'created')->sole()->changes['after']['price']
        );

        $product = Product::findOrFail($id);

        $this->putJson("/api/products/{$id}", [
            'name' => $product->name,
            'description' => $product->description,
            'image' => $product->image,
            'price' => 59.9,
            'category' => $product->category,
            'stock' => $product->stock,
        ])->assertStatus(200);

        $log = ProductLog::where('product_id', $id)->where('action', 'updated')->sole();

        $this->assertSame('49.90', $log->changes['before']['price']);
        $this->assertSame('59.90', $log->changes['after']['price']);
    }

    public function test_excluir_produto_mantem_log_com_product_id_null(): void
    {
        $user = $this->authUser();
        $product = Product::factory()->create(['name' => 'Camiseta Apagada']);

        $this->deleteJson("/api/products/{$product->id}")->assertStatus(204);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        $log = ProductLog::where('action', 'deleted')->sole();

        $this->assertNull($log->product_id);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame($product->id, $log->changes['snapshot']['id']);
        $this->assertSame('Camiseta Apagada', $log->changes['snapshot']['name']);
    }
}
