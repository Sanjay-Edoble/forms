<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Logger;

/**
 * Simple file-based rate limiting for sensitive endpoints.
 */
class RateLimitMiddleware
{
    private int $maxAttempts;
    private int $windowSeconds;

    public function __construct(int $maxAttempts = 10, int $windowSeconds = 60)
    {
        $this->maxAttempts = $maxAttempts;
        $this->windowSeconds = $windowSeconds;
    }

    public function handle(Request $request): bool
    {
        $key = md5($request->ip() . ':' . $request->path());
        $cacheDir = BASE_PATH . '/storage/cache/rate_limit';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir . '/' . $key . '.json';
        $now = time();
        $data = ['attempts' => [], 'blocked_until' => 0];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?? $data;
        }

        // Check if currently blocked
        if ($data['blocked_until'] > $now) {
            $retryAfter = $data['blocked_until'] - $now;
            Logger::security('Rate limit exceeded', [
                'ip'   => $request->ip(),
                'path' => $request->path(),
            ]);

            if ($request->isAjax()) {
                Response::json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$retryAfter} seconds.",
                ], 429);
            }

            flash('error', "Too many attempts. Please try again in {$retryAfter} seconds.");
            redirect($request->path());
        }

        // Clean old attempts outside the window
        $data['attempts'] = array_filter($data['attempts'], fn($t) => $t > ($now - $this->windowSeconds));

        // Check if exceeded
        if (count($data['attempts']) >= $this->maxAttempts) {
            $data['blocked_until'] = $now + $this->windowSeconds;
            file_put_contents($file, json_encode($data), LOCK_EX);

            if ($request->isAjax()) {
                Response::json([
                    'success' => false,
                    'message' => 'Too many attempts. Please try again later.',
                ], 429);
            }

            flash('error', 'Too many attempts. Please try again later.');
            redirect($request->path());
        }

        // Record this attempt
        $data['attempts'][] = $now;
        file_put_contents($file, json_encode($data), LOCK_EX);

        return true;
    }
}
