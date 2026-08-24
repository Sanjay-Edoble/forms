<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Core\Response;

/**
 * Restricts access to admin panel based on ADMIN_EMAILS env var.
 */
class AdminMiddleware
{
    public function handle(Request $request): bool
    {
        $user = Session::get('user');
        if (!$user) {
            redirect('/login');
        }

        $adminEmails = config('app.admin_emails', []);
        if (!in_array($user['email'] ?? '', $adminEmails)) {
            Response::forbidden('You do not have access to this area.');
        }

        return true;
    }
}
