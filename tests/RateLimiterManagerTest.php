<?php

namespace RahulTiwari\RedisThrottle\Tests;

use Illuminate\Support\Facades\Redis;
use RahulTiwari\RedisThrottle\RateLimiterManager;

class RateLimiterManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_allows_requests_under_the_limit(): void
    {
        $limiter = new RateLimiterManager();

        $result = $limiter->hit('test:key', maxHits: 5, decaySeconds: 60);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(1, $result['hits']);
        $this->assertEquals(4, $result['remaining']);
    }

    public function test_blocks_requests_over_the_limit(): void
    {
        $limiter = new RateLimiterManager();

        for ($i = 0; $i < 3; $i++) {
            $limiter->hit('test:key', maxHits: 3, decaySeconds: 60);
        }

        $result = $limiter->hit('test:key', maxHits: 3, decaySeconds: 60);

        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
    }

    public function test_reset_clears_the_bucket(): void
    {
        $limiter = new RateLimiterManager();

        $limiter->hit('test:key', maxHits: 1, decaySeconds: 60);
        $limiter->reset('test:key');

        $result = $limiter->hit('test:key', maxHits: 1, decaySeconds: 60);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(1, $result['hits']);
    }

    public function test_different_keys_have_independent_buckets(): void
    {
        $limiter = new RateLimiterManager();

        $limiter->hit('test:key-a', maxHits: 1, decaySeconds: 60);
        $result = $limiter->hit('test:key-b', maxHits: 1, decaySeconds: 60);

        $this->assertTrue($result['allowed']);
    }
}
