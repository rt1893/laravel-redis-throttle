<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default rate limit
    |--------------------------------------------------------------------------
    |
    | Used when a route applies the "redis.throttle" middleware without
    | explicit parameters, e.g. Route::middleware('redis.throttle').
    |
    */

    'default_max_attempts' => 60,

    'default_decay_minutes' => 1,

    /*
    |--------------------------------------------------------------------------
    | Redis connection
    |--------------------------------------------------------------------------
    |
    | Which Redis connection (as defined in config/database.php) the
    | limiter should use for its counters.
    |
    */

    'connection' => env('REDIS_THROTTLE_CONNECTION', 'default'),

];
