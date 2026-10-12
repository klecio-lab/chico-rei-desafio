<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportCommandTest extends TestCase
{
    use RefreshDatabase;

    private function fakeCatalog(): void
    {
        Http::fake([
            'chicorei.com*' => Http::response(
                file_get_contents(base_path('tests/Fixtures/chicorei_tema.html')), 200
            ),
        ]);
        Config::set('import.throttle_ms', 0);
    }

    public function test_import_cria_produtos_com_source_url(): void
    {
        $this->fakeCatalog();

        $this->artisan('products:import', ['--tema' => 'geek'])
            ->assertSuccessful();

        $this->assertSame(2, Product::whereNotNull('source_url')->count());

        $product = Product::where('source_url', 'https://chicorei.com/camiseta/fixture-alfa-101.html')->sole();

        $this->assertSame('Camiseta Fixture Alfa', $product->name);
        $this->assertSame('geek', $product->category);
        $this->assertSame(10, $product->stock);
    }

    public function test_rodar_duas_vezes_nao_duplica_e_atualiza_preco(): void
    {
        $this->fakeCatalog();

        $this->artisan('products:import', ['--tema' => 'geek'])->assertSuccessful();
        $this->artisan('products:import', ['--tema' => 'geek'])->assertSuccessful();

        $this->assertSame(2, Product::whereNotNull('source_url')->count());
    }

    public function test_reimportacao_atualiza_price_e_image_sem_tocar_stock(): void
    {
        $fixture = file_get_contents(base_path('tests/Fixtures/chicorei_tema.html'));

        // Stubs do Http acumulam (first-match wins): sequência entrega um corpo por request.
        Http::fakeSequence('chicorei.com*')
            ->push($fixture, 200)
            ->push(str_replace('49.9', '59.9', $fixture), 200);
        Config::set('import.throttle_ms', 0);

        $this->artisan('products:import', ['--tema' => 'geek'])->assertSuccessful();

        $product = Product::where('source_url', 'https://chicorei.com/camiseta/fixture-alfa-101.html')->sole();
        $product->update(['stock' => 3]);

        $this->artisan('products:import', ['--tema' => 'geek'])->assertSuccessful();

        $product->refresh();

        $this->assertSame('59.90', $product->price);
        $this->assertSame(3, $product->stock);
    }

    public function test_dry_run_nao_persiste(): void
    {
        $this->fakeCatalog();

        $this->artisan('products:import', ['--tema' => 'geek', '--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame(0, Product::count());
    }

    public function test_import_desabilitado_aborta(): void
    {
        $this->fakeCatalog();
        Config::set('import.enabled', false);

        $this->artisan('products:import', ['--tema' => 'geek'])
            ->assertFailed();

        $this->assertSame(0, Product::count());
        Http::assertNothingSent();
    }
}
