<?php

namespace App\Core;

/**
 * HTTP Response helpers.
 */
class Response
{
    /**
     * Send a JSON response.
     */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send a redirect response.
     */
    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Send a rendered view as the response.
     */
    public static function view(string $name, array $data = [], ?string $layout = null, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo View::render($name, $data, $layout);
        exit;
    }

    /**
     * Send a file download.
     */
    public static function download(string $content, string $filename, string $mimeType = 'application/octet-stream'): never
    {
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    /**
     * Send a 404 Not Found.
     */
    public static function notFound(string $message = 'Page not found'): never
    {
        if (self::wantsJson()) {
            self::json(['success' => false, 'message' => $message], 404);
        }
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo View::render('errors.404', ['message' => $message]);
        exit;
    }

    /**
     * Send a 403 Forbidden.
     */
    public static function forbidden(string $message = 'Access denied'): never
    {
        if (self::wantsJson()) {
            self::json(['success' => false, 'message' => $message], 403);
        }
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        echo View::render('errors.403', ['message' => $message]);
        exit;
    }

    /**
     * Check if the client expects JSON.
     */
    private static function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
