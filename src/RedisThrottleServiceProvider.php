<?php

namespace RahulTiwari\RedisThrottle;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use RahulTiwari\RedisThrottle\Middleware\ThrottleRequests;

class RedisThrottleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rate-limiter.php', 'rate-limiter');

        $this->app->singleton(RateLimiterManager::class, fn () => new RateLimiterManager());
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('redis.throttle', ThrottleRequests::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/rate-limiter.php' => config_path('rate-limiter.php'),
            ], 'config');
        }
    }
}
