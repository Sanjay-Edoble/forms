<?php
/**
 * Edoble Forms — Global Helper Functions
 */

if (!function_exists('env')) {
    /**
     * Get an environment variable value.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }
        // Cast common string booleans
        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            default            => $value,
        };
    }
}

if (!function_exists('config')) {
    /**
     * Get a config value using dot notation: config('app.name')
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        $parts = explode('.', $key);
        $file  = array_shift($parts);

        if (!isset($configs[$file])) {
            $path = BASE_PATH . '/config/' . $file . '.php';
            $configs[$file] = file_exists($path) ? require $path : [];
        }

        $value = $configs[$file];
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}

if (!function_exists('view')) {
    /**
     * Render a view file.
     */
    function view(string $name, array $data = [], ?string $layout = null): string
    {
        return \App\Core\View::render($name, $data, $layout);
    }
}

if (!function_exists('redirect')) {
    /**
     * Send a redirect response.
     */
    function redirect(string $url, int $status = 302): never
    {
        \App\Core\Response::redirect($url, $status);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a full URL to a path.
     */
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a URL to a public asset.
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the current CSRF token.
     */
    function csrf_token(): string
    {
        return \App\Core\CSRF::token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a hidden CSRF input field.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old form input value.
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return \App\Core\Session::getFlash('old_input.' . $key, $default);
    }
}

if (!function_exists('session')) {
    /**
     * Get / set session values.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return \App\Core\Session::class;
        }
        return \App\Core\Session::get($key, $default);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities.
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die — debug helper.
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit(1);
    }
}

if (!function_exists('flash')) {
    /**
     * Set a flash message.
     */
    function flash(string $key, mixed $value): void
    {
        \App\Core\Session::setFlash($key, $value);
    }
}

if (!function_exists('get_flash')) {
    /**
     * Get and consume a flash message.
     */
    function get_flash(string $key, mixed $default = null): mixed
    {
        return \App\Core\Session::getFlash($key, $default);
    }
}

if (!function_exists('is_authenticated')) {
    /**
     * Check if the user is authenticated.
     */
    function is_authenticated(): bool
    {
        return !empty(\App\Core\Session::get('user_token'));
    }
}

if (!function_exists('current_user')) {
    /**
     * Get the current authenticated user data.
     */
    function current_user(): ?array
    {
        return \App\Core\Session::get('user');
    }
}

if (!function_exists('current_user_role')) {
    /**
     * Get the current user's role in the active workspace.
     */
    function current_user_role(): string
    {
        return \App\Core\Session::get('current_workspace_role', 'admin');
    }
}

if (!function_exists('generate_id')) {
    /**
     * Generate a short unique ID for forms, questions, etc.
     */
    function generate_id(string $prefix = ''): string
    {
        return $prefix . bin2hex(random_bytes(8));
    }
}

if (!function_exists('time_ago')) {
    /**
     * Human-readable time ago string.
     */
    function time_ago(string $datetime): string
    {
        $now  = new \DateTime();
        $then = new \DateTime($datetime);
        $diff = $now->diff($then);

        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'just now';
    }
}
