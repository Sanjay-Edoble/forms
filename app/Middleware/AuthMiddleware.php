<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Core\Response;

/**
 * Ensures user is authenticated. Redirects to login if not.
 */
class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Session::has('user_token') || !Session::has('user')) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            }
            Session::setFlash('error', 'Please log in to continue.');
            Session::set('intended_url', $request->path());
            redirect('/login');
        }
        return true;
    }
}
