<?php

namespace App\Core;

/**
 * CSRF protection — token generation and validation.
 */
class CSRF
{
    /**
     * Get or generate a CSRF token for the current session.
     */
    public static function token(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Validate a submitted CSRF token.
     */
    public static function validate(?string $submitted): bool
    {
        $expected = Session::get('_csrf_token');
        if (!$expected || !$submitted) {
            return false;
        }
        return hash_equals($expected, $submitted);
    }

    /**
     * Regenerate the CSRF token (call after successful submission).
     */
    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);
        return $token;
    }
}
