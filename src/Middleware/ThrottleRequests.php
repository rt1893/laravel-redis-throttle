<?php

namespace RahulTiwari\RedisThrottle\Middleware;

use Closure;
use Illuminate\Http\Request;
use RahulTiwari\RedisThrottle\RateLimiterManager;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    public function __construct(protected RateLimiterManager $limiter)
    {
    }

    /**
     * Usage in routes:
     *   Route::middleware('redis.throttle:60,1')->group(...);   // 60 requests / 1 minute
     *   Route::middleware('redis.throttle:5,1,login')->post(...); // named bucket, 5/min
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  int  $maxAttempts   Max requests allowed in the window
     * @param  int  $decayMinutes  Window length in minutes
     * @param  string|null  $bucket  Optional named bucket; defaults to the route name/URI
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1, ?string $bucket = null)
    {
        $key = $this->resolveKey($request, $bucket);

        $result = $this->limiter->hit($key, $maxAttempts, $decayMinutes * 60);

        if (! $result['allowed']) {
            return response()->json([
                'message' => 'Too many requests.',
            ], Response::HTTP_TOO_MANY_REQUESTS)->withHeaders([
                'X-RateLimit-Limit'     => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'Retry-After'           => $result['retryAfter'],
            ]);
        }

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => $result['remaining'],
        ]);
    }

    protected function resolveKey(Request $request, ?string $bucket): string
    {
        $identity = $request->user()?->getAuthIdentifier() ?? $request->ip();
        $scope = $bucket ?? ($request->route()?->getName() ?? $request->path());

        return "user:{$identity}:route:{$scope}";
    }
}
