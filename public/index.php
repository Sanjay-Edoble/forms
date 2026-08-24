<?php
/**
 * Edoble Forms — Front Controller
 * All requests are routed through this file.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoloader
require BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Timezone
date_default_timezone_set(config('app.timezone'));

// Error handling
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Start session
\App\Core\Session::start();

// Create request
$request = new \App\Core\Request();

// Create router
$router = new \App\Core\Router();

// Load routes
require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/api.php';

// Dispatch
$router->dispatch($request);
