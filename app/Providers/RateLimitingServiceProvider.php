<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RateLimitingServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        foreach (config('rate_limiters', []) as $rateLimiter) {
            $this->app->make($rateLimiter)->register();
        }
    }
}
