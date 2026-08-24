<?php

namespace App\Core;

/**
 * HTTP Request wrapper.
 */
class Request
{
    private array $query;
    private array $body;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->query  = $_GET;
        $this->body   = $_POST;
        $this->files  = $_FILES;
        $this->server = $_SERVER;
    }

    /**
     * Get the HTTP method (uppercase).
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get the request path (without query string).
     */
    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return '/' . trim($path, '/');
    }

    /**
     * Get a request input value (query or body).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        // Try JSON body first
        if ($this->isJson()) {
            $json = $this->json();
            if (isset($json[$key])) {
                return $json[$key];
            }
        }
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all input data.
     */
    public function all(): array
    {
        if ($this->isJson()) {
            return array_merge($this->query, $this->json());
        }
        return array_merge($this->query, $this->body);
    }

    /**
     * Get a query parameter.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get an uploaded file.
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get a request header.
     */
    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? null;
    }

    /**
     * Check if request is AJAX (XHR or Fetch).
     */
    public function isAjax(): bool
    {
        return ($this->header('X-Requested-With') === 'XMLHttpRequest')
            || (str_contains($this->header('Accept') ?? '', 'application/json'));
    }

    /**
     * Check if the request has JSON content type.
     */
    public function isJson(): bool
    {
        return str_contains($this->server['CONTENT_TYPE'] ?? '', 'application/json');
    }

    /**
     * Get JSON request body.
     */
    public function json(): array
    {
        static $parsed = null;
        if ($parsed === null) {
            $raw = file_get_contents('php://input');
            $parsed = json_decode($raw, true) ?? [];
        }
        return $parsed;
    }

    /**
     * Get client IP (with proxy support).
     */
    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['HTTP_X_REAL_IP']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }
}
