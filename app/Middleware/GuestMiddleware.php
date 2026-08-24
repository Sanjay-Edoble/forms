<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

/**
 * Prevents authenticated users from accessing guest-only pages (login, register).
 */
class GuestMiddleware
{
    public function handle(Request $request): bool
    {
        if (Session::has('user_token') && Session::has('user')) {
            redirect('/dashboard');
        }
        return true;
    }
}
