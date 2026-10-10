<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_publico_retorna_envelope_com_meta(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'description', 'image', 'price', 'category', 'stock', 'created_at', 'updated_at']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_show_publico_retorna_produto_com_logs(): void
    {
        $product = Product::factory()->create(['name' => 'Camiseta Teste']);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Camiseta Teste')
            ->assertJsonStructure(['data' => ['id', 'name', 'price', 'logs']])
            ->assertJsonPath('data.logs', []);
    }

    public function test_show_inexistente_retorna_404_ptbr(): void
    {
        $response = $this->getJson('/api/products/999999');

        $response->assertStatus(404)->assertJsonPath('message', 'Produto não encontrado.');
    }

    public function test_index_filtra_por_q(): void
    {
        Product::factory()->create(['name' => 'Camiseta Galáxia']);
        Product::factory()->create(['name' => 'Caneca Térmica']);

        $response = $this->getJson('/api/products?q=galaxia');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Camiseta Galáxia');
    }

    public function test_index_filtra_por_categoria_preco_e_estoque(): void
    {
        Product::factory()->create(['category' => 'camisetas', 'price' => 50, 'stock' => 10]);
        Product::factory()->create(['category' => 'camisetas', 'price' => 200, 'stock' => 0]);
        Product::factory()->create(['category' => 'canecas', 'price' => 60, 'stock' => 5]);

        $this->getJson('/api/products?category=camisetas')
            ->assertStatus(200)->assertJsonPath('meta.total', 2);

        $this->getJson('/api/products?min_price=55&max_price=100')
            ->assertStatus(200)->assertJsonPath('meta.total', 1);

        $this->getJson('/api/products?in_stock=1')
            ->assertStatus(200)->assertJsonPath('meta.total', 2);

        $this->getJson('/api/products?in_stock=0')
            ->assertStatus(200)->assertJsonPath('meta.total', 1);
    }

    public function test_index_ordena_e_pagina(): void
    {
        Product::factory()->create(['price' => 30]);
        Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 20]);

        $this->getJson('/api/products?sort=price')
            ->assertStatus(200)
            ->assertJsonPath('data.0.price', '10.00')
            ->assertJsonPath('data.2.price', '30.00');

        $this->getJson('/api/products?sort=-price&per_page=2')
            ->assertStatus(200)
            ->assertJsonPath('data.0.price', '30.00')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_index_com_sort_invalido_retorna_422(): void
    {
        $response = $this->getJson('/api/products?sort=invalid');

        $response->assertStatus(422)->assertJsonValidationErrors(['sort']);
    }

    public function test_index_com_per_page_invalido_retorna_422(): void
    {
        $this->getJson('/api/products?per_page=0')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
        $this->getJson('/api/products?per_page=101')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_com_max_menor_que_min_retorna_422(): void
    {
        $response = $this->getJson('/api/products?min_price=100&max_price=10');

        $response->assertStatus(422)->assertJsonValidationErrors(['max_price']);
        $this->assertSame(
            'O preço máximo deve ser maior ou igual ao preço mínimo.',
            $response->json('errors.max_price.0')
        );
    }
}
