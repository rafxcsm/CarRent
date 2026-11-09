<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
{
    RateLimiter::for('api', function (Request $request) {
        $actor = $request->user();
        $key = $actor
            ? $actor->getMorphClass().':'.$actor->getAuthIdentifier()
            : (string) $request->ip();

        return Limit::perMinute(120)->by($key);
    });
}

}
