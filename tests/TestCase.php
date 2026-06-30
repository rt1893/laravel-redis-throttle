<?php

namespace RahulTiwari\RedisThrottle\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use RahulTiwari\RedisThrottle\RedisThrottleServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [RedisThrottleServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.redis.client', 'predis');
        $app['config']->set('database.redis.default', [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ]);
    }
}
