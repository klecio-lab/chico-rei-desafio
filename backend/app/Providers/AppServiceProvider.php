<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Expiração curta do personal token de login (BDR-001, valor em config/api.php).
        Passport::personalAccessTokensExpireIn(
            now()->addDays(config('api.token_expires_days'))
        );

        // Bucket próprio do login: 10/min por IP (BDR-003, valores em config/api.php).
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(config('api.throttle_login_max'))->by($request->ip());
        });
    }
}
