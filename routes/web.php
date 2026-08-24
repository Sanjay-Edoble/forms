<?php
/**
 * Edoble Forms — Web Routes
 * @var \App\Core\Router $router
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\FormController;
use App\Controllers\BuilderController;
use App\Controllers\PublicFormController;
use App\Controllers\PreviewController;
use App\Controllers\ResponseController;
use App\Controllers\AnalyticsController;
use App\Controllers\SettingsController;
use App\Controllers\ShareController;
use App\Controllers\TemplateController;
use App\Controllers\TrashController;
use App\Controllers\ExportController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\CSRFMiddleware;
use App\Middleware\AdminMiddleware;

// ─── Public Routes ────────────────────────────────────────────

// Home redirect
$router->get('/', function() {
    redirect(is_authenticated() ? '/dashboard' : '/login');
});

// Public form
$router->get('/f/{id}', [PublicFormController::class, 'show']);
$router->post('/f/{id}/gate', [PublicFormController::class, 'gate']);
$router->post('/f/{id}/submit', [PublicFormController::class, 'submit']);

// Embed
$router->get('/embed/{id}', [PublicFormController::class, 'embed']);

// ─── Guest Routes (login, register) ──────────────────────────

$router->group(['middleware' => [GuestMiddleware::class]], function($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Email verification (can be accessed whether logged in or not)
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);

// Logout (must be authenticated)
$router->post('/logout', [AuthController::class, 'logout']);

// ─── Authenticated Routes ─────────────────────────────────────

$router->group(['middleware' => [AuthMiddleware::class, CSRFMiddleware::class]], function($router) {
    
    // Dashboard
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // Forms
    $router->get('/forms', [FormController::class, 'index']);
    $router->get('/forms/create', [FormController::class, 'create']);
    $router->get('/forms/{id}', function($req, $params) { redirect('/f/' . $params['id']); });
    $router->post('/forms', [FormController::class, 'store']);
    $router->post('/forms/{id}/duplicate', [FormController::class, 'duplicate']);
    $router->post('/forms/{id}/delete', [FormController::class, 'delete']);
    $router->post('/forms/{id}/status', [FormController::class, 'updateStatus']);

    // Form Builder
    $router->get('/forms/{id}/edit', [BuilderController::class, 'index']);

    // Preview
    $router->get('/forms/{id}/preview', [PreviewController::class, 'index']);

    // Responses (Live Data Sheet)
    $router->get('/forms/{id}/responses', [ResponseController::class, 'index']);
    $router->get('/forms/{id}/responses/{responseId}', [ResponseController::class, 'show']);
    $router->post('/forms/{id}/responses/{responseId}/edit', [ResponseController::class, 'update']);
    $router->post('/forms/{id}/responses/{responseId}/delete', [ResponseController::class, 'delete']);
    $router->post('/forms/{id}/responses/bulk-delete', [ResponseController::class, 'bulkDelete']);

    // Analytics
    $router->get('/forms/{id}/analytics', [AnalyticsController::class, 'index']);

    // Settings
    $router->get('/forms/{id}/settings', [SettingsController::class, 'formSettings']);
    $router->post('/forms/{id}/settings', [SettingsController::class, 'updateFormSettings']);

    // Sharing
    $router->get('/forms/{id}/share', [ShareController::class, 'index']);

    // Export
    $router->get('/forms/{id}/export/csv', [ExportController::class, 'csv']);
    $router->get('/forms/{id}/export/excel', [ExportController::class, 'excel']);

    // Templates
    $router->get('/templates', [TemplateController::class, 'index']);
    $router->post('/templates/{id}/use', [TemplateController::class, 'useTemplate']);

    // Trash
    $router->get('/trash', [TrashController::class, 'index']);
    $router->post('/trash/{id}/restore', [TrashController::class, 'restore']);
    $router->post('/trash/{id}/delete', [TrashController::class, 'permanentDelete']);

    // Account Settings
    $router->get('/settings', [SettingsController::class, 'accountSettings']);
    $router->post('/settings', [SettingsController::class, 'updateAccountSettings']);
});

// ─── Admin Routes ─────────────────────────────────────────────

$router->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function($router) {
    $router->get('/', [AdminController::class, 'index']);
    $router->get('/users', [AdminController::class, 'users']);
    $router->get('/forms', [AdminController::class, 'forms']);
    $router->get('/logs', [AdminController::class, 'logs']);
});
