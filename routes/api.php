<?php
/**
 * Edoble Forms — Internal API Routes
 * Used by AJAX calls from the frontend (builder autosave, live data sheet, etc.)
 * @var \App\Core\Router $router
 */

use App\Controllers\BuilderController;
use App\Controllers\ResponseController;
use App\Controllers\FormController;
use App\Middleware\AuthMiddleware;

$router->group(['prefix' => '/api', 'middleware' => [AuthMiddleware::class]], function($router) {
    
    // Builder autosave
    $router->post('/forms/{id}/save', [BuilderController::class, 'save']);
    $router->post('/forms/{id}/save-meta', [BuilderController::class, 'saveMeta']);
    $router->post('/forms/{id}/save-settings', [BuilderController::class, 'saveSettings']);
    $router->post('/forms/{id}/save-theme', [BuilderController::class, 'saveTheme']);

    // Live Data Sheet polling
    $router->get('/forms/{id}/responses', [ResponseController::class, 'apiList']);
    $router->get('/forms/{id}/responses/poll', [ResponseController::class, 'poll']);
    $router->get('/forms/{id}/responses/{responseId}', [ResponseController::class, 'apiShow']);

    // Form stats (for dashboard)
    $router->get('/forms/{id}/stats', [FormController::class, 'stats']);
    
    // Quick actions
    $router->post('/forms/{id}/publish', [FormController::class, 'publish']);
    $router->post('/forms/{id}/unpublish', [FormController::class, 'unpublish']);
});
