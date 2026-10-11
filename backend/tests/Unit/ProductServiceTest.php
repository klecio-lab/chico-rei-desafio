<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_usa_per_page_do_config_quando_ausente(): void
    {
        Config::set('api.pagination_default', 5);
        Product::factory()->count(7)->create();

        $paginator = app(ProductService::class)->paginate([]);

        $this->assertSame(5, $paginator->perPage());
        $this->assertSame(7, $paginator->total());
        $this->assertSame(2, $paginator->lastPage());
    }

    public function test_paginate_ordena_mais_recentes_primeiro_por_padrao(): void
    {
        $old = Product::factory()->create(['created_at' => now()->subDay()]);
        $new = Product::factory()->create();

        $items = app(ProductService::class)->paginate([])->items();

        $this->assertSame($new->id, $items[0]->id);
        $this->assertSame($old->id, $items[1]->id);
    }
}
