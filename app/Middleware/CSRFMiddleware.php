<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\CSRF;
use App\Core\Response;
use App\Helpers\Logger;

/**
 * Validates CSRF token on state-changing requests.
 */
class CSRFMiddleware
{
    public function handle(Request $request): bool
    {
        $method = $request->method();

        // Only validate on state-changing methods
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // Skip CSRF for API routes (they use API key auth instead)
        if (str_starts_with($request->path(), '/api/')) {
            return true;
        }

        $token = $request->input('_csrf_token') ?? $request->header('X-CSRF-Token');

        if (!CSRF::validate($token)) {
            Logger::security('CSRF validation failed', [
                'ip'   => $request->ip(),
                'path' => $request->path(),
            ]);

            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Invalid CSRF token. Please refresh the page.'], 403);
            }

            flash('error', 'Your session has expired. Please try again.');
            redirect($request->path());
        }

        return true;
    }
}
