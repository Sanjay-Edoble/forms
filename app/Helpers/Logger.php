<?php

namespace App\Helpers;

/**
 * Simple file-based logger with daily rotation.
 */
class Logger
{
    private static ?string $logDir = null;

    private static function dir(): string
    {
        if (self::$logDir === null) {
            self::$logDir = BASE_PATH . '/storage/logs';
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
        }
        return self::$logDir;
    }

    /**
     * Write a log entry.
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $file = self::dir() . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        $line = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Never log passwords or secret keys
        $line = preg_replace('/edb_sk_[a-zA-Z0-9]+/', 'edb_sk_***', $line);
        $line = preg_replace('/"password"\s*:\s*"[^"]*"/', '"password":"***"', $line);

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function security(string $message, array $context = []): void
    {
        self::log('SECURITY', $message, $context);
    }
}
