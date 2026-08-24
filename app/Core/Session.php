<?php

namespace App\Core;

/**
 * Secure session management with flash messages.
 */
class Session
{
    private static bool $started = false;

    /**
     * Start the session if not already started.
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $secure = (bool) config('app.session.secure', false);
        $lifetime = (int) config('app.session.lifetime', 120) * 60;

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly'  => true,
            'samesite'  => 'Lax',
        ]);

        session_name('edoble_session');
        session_start();
        self::$started = true;

        // Consume flash messages from previous request
        self::consumeFlash();
    }

    /**
     * Get a session value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value.
     */
    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Remove a session key.
     */
    public static function remove(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Check if a session key exists.
     */
    public static function has(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Set a flash message (available only on the next request).
     */
    public static function setFlash(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION['_flash_next'][$key] = $value;
    }

    /**
     * Get a flash message.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION['_flash_current'][$key] ?? $default;
    }

    /**
     * Move next-request flash data to current, clear old.
     */
    private static function consumeFlash(): void
    {
        $_SESSION['_flash_current'] = $_SESSION['_flash_next'] ?? [];
        $_SESSION['_flash_next'] = [];
    }

    /**
     * Regenerate session ID (call after login).
     */
    public static function regenerate(): void
    {
        self::ensureStarted();
        session_regenerate_id(true);
    }

    /**
     * Destroy the session completely (logout).
     */
    public static function destroy(): void
    {
        self::ensureStarted();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    private static function ensureStarted(): void
    {
        if (!self::$started) {
            self::start();
        }
    }
}
