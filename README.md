# Laravel Redis Throttle

[![Tests](https://img.shields.io/badge/tests-passing-5eead4)](#)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

Configurable, Redis-backed rate limiting for Laravel APIs — per-user, per-route, with standard `X-RateLimit-*` response headers and `429 Too Many Requests` handling out of the box.

Laravel ships a default throttle middleware, but it doesn't expose per-route bucket naming, custom retry headers, or a programmatic API for resetting limits (e.g. from a support panel). This package fills that gap with a small, dependency-light implementation built on Redis fixed-window counters.

## Why

Built out of real production need: rate-limiting onboarding and search endpoints on a high-traffic backend system, where the stock `throttle` middleware wasn't flexible enough to scope limits per feature without duplicating route groups.

## Installation

```bash
composer require rahultiwari/laravel-redis-throttle
```

The service provider is auto-discovered. Optionally publish the config:

```bash
php artisan vendor:publish --tag=config
```

## Usage

Apply the middleware to any route or group:

```php
// 60 requests per minute (defaults)
Route::middleware('redis.throttle')->get('/profile', ...);

// 5 requests per minute, custom limit
Route::middleware('redis.throttle:5,1')->post('/login', ...);

// Named bucket — useful when multiple routes should share one limit
Route::middleware('redis.throttle:100,1,search')->group(function () {
    Route::get('/search', ...);
    Route::get('/search/suggest', ...);
});
```

Each limited response includes standard headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 42
```

When the limit is exceeded:

```
HTTP/1.1 429 Too Many Requests
Retry-After: 38
```

```json
{ "message": "Too many requests." }
```

## Resetting a limit programmatically

Useful for support tooling — e.g. unblocking a user manually.

```php
use RahulTiwari\RedisThrottle\RateLimiterManager;

app(RateLimiterManager::class)->reset('user:42:route:login');
```

## Configuration

Published to `config/rate-limiter.php`:

```php
return [
    'default_max_attempts' => 60,
    'default_decay_minutes' => 1,
    'connection' => env('REDIS_THROTTLE_CONNECTION', 'default'),
];
```

## Testing

```bash
composer install
vendor/bin/phpunit
```

Requires a local Redis instance (database index `1` is used by the test suite to avoid clobbering real data).

## License

MIT © [Rahul Tiwari](https://github.com/rahultiwari)
