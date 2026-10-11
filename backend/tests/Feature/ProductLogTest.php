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
        $this->assertSame(75, $log->changes['after']['price']);
        $this->assertArrayNotHasKey('stock', $log->changes['after']);
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
