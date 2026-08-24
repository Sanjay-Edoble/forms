<?php

namespace App\Core;

/**
 * Simple PHP view/template engine with layouts and sections.
 */
class View
{
    private static string $viewsPath = '';
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static ?string $layoutName = null;
    private static array $layoutData = [];

    /**
     * Render a view with optional layout.
     */
    public static function render(string $name, array $data = [], ?string $layout = null): string
    {
        self::$viewsPath = BASE_PATH . '/views';
        self::$sections = [];
        self::$layoutName = $layout;
        self::$layoutData = $data;

        // Render the view content
        $content = self::renderFile($name, $data);

        // If a layout was set during rendering (via extend()), wrap in layout
        if (self::$layoutName) {
            self::$sections['content'] = $content;
            $layoutData = array_merge($data, ['sections' => self::$sections]);
            return self::renderFile(self::$layoutName, $layoutData);
        }

        return $content;
    }

    /**
     * Render a view file and return its output.
     */
    private static function renderFile(string $name, array $data): string
    {
        $filePath = self::$viewsPath . '/' . str_replace('.', '/', $name) . '.php';

        if (!file_exists($filePath)) {
            // Fallback for error pages
            return self::renderErrorPage($name, $data);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $filePath;
        return ob_get_clean();
    }

    /**
     * Set the layout to extend (called from within a view).
     */
    public static function extend(string $layout): void
    {
        self::$layoutName = $layout;
    }

    /**
     * Start a named section (called from within a view).
     */
    public static function section(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * End the current section.
     */
    public static function endSection(): void
    {
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Yield a section's content (called from layout).
     */
    public static function yield(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Include a partial view.
     */
    public static function partial(string $name, array $data = []): string
    {
        return self::renderFile($name, array_merge(self::$layoutData, $data));
    }

    /**
     * Render a minimal error page.
     */
    private static function renderErrorPage(string $name, array $data): string
    {
        $message = $data['message'] ?? 'An error occurred';
        $code = str_contains($name, '404') ? '404' : (str_contains($name, '403') ? '403' : '500');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$code} — Edoble Forms</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Inter', sans-serif; background: #0f1117; color: #e4e4e7; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .error-container { text-align: center; }
                .error-code { font-size: 120px; font-weight: 800; background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; }
                .error-message { font-size: 18px; color: #a1a1aa; margin-top: 12px; }
                .error-link { display: inline-block; margin-top: 24px; padding: 10px 24px; background: #6366f1; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.2s; }
                .error-link:hover { background: #4f46e5; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-code">{$code}</div>
                <div class="error-message">{$message}</div>
                <a href="/" class="error-link">Go Home</a>
            </div>
        </body>
        </html>
        HTML;
    }
}
