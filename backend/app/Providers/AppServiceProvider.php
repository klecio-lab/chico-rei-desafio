<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Services\Import\ChicoReiFetcher;
use App\Services\Import\ProductImportFetcherInterface;
use App\Services\Search\ElasticProductSearch;
use App\Services\Search\ProductSearchInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Trocar de provedor = implementar a interface + trocar config (BDR-009).
        $this->app->bind(
            ProductImportFetcherInterface::class,
            ChicoReiFetcher::class,
        );

        // Busca full-text com fallback LIKE; desligada por padrão (BDR-012).
        $this->app->bind(
            ProductSearchInterface::class,
            ElasticProductSearch::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Catálogo coletivo: eventos do model viram logs async (BDR-005).
        Product::observe(ProductObserver::class);

        // Expiração curta do personal token de login (BDR-001, valor em config/api.php).
        Passport::personalAccessTokensExpireIn(
            now()->addDays(config('api.token_expires_days'))
        );

        // Bucket próprio do login: 10/min por IP (BDR-003, valores em config/api.php).
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(config('api.throttle_login_max'))->by($request->ip());
        });

        // Leitura pública do catálogo: bucket generoso por IP ou usuário logado.
        RateLimiter::for('products-read', function (Request $request) {
            return Limit::perMinute(config('api.throttle_read_max'))
                ->by($request->user()?->id ?? $request->ip());
        });

        // Escrita protegida: só chega aqui com auth:api, então sempre há usuário.
        RateLimiter::for('products-write', function (Request $request) {
            return Limit::perMinute(config('api.throttle_write_max'))
                ->by($request->user()?->id ?? $request->ip());
        });
    }
}
