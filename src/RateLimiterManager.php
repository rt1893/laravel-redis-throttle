<?php

namespace RahulTiwari\RedisThrottle;

use Illuminate\Support\Facades\Redis;

class RateLimiterManager
{
    /**
     * Attempt a hit against the limiter for the given key.
     *
     * Uses a fixed-window counter in Redis: INCR + EXPIRE, which is O(1)
     * per request and avoids storing per-request timestamps.
     *
     * @param  string  $key        Unique key identifying the limiter bucket (e.g. "user:42:route:orders.store")
     * @param  int     $maxHits    Maximum number of allowed hits within the window
     * @param  int     $decaySeconds Window length in seconds
     * @return array{allowed: bool, hits: int, remaining: int, retryAfter: int}
     */
    public function hit(string $key, int $maxHits, int $decaySeconds): array
    {
        $redisKey = $this->prefixedKey($key);

        $hits = Redis::incr($redisKey);

        if ($hits === 1) {
            Redis::expire($redisKey, $decaySeconds);
        }

        $ttl = Redis::ttl($redisKey);
        $ttl = $ttl > 0 ? $ttl : $decaySeconds;

        return [
            'allowed'    => $hits <= $maxHits,
            'hits'       => $hits,
            'remaining'  => max(0, $maxHits - $hits),
            'retryAfter' => $ttl,
        ];
    }

    /**
     * Reset the limiter bucket for a given key. Useful in tests or
     * after a manual override (e.g. support unblocking a user).
     */
    public function reset(string $key): void
    {
        Redis::del($this->prefixedKey($key));
    }

    protected function prefixedKey(string $key): string
    {
        return 'redis_throttle:' . $key;
    }
}
