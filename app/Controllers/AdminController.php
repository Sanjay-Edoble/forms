<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\EdobaseClient;

class AdminController
{
    public function index(Request $request, array $params): void
    {
        $client = new EdobaseClient();
        $health = $client->health();
        $collections = $client->listCollections();

        echo view('admin.index', [
            'pageTitle'   => 'Admin Panel',
            'health'      => $health,
            'collections' => $collections['data'] ?? [],
        ], 'layouts.app');
        exit;
    }

    public function users(Request $request, array $params): void
    {
        echo view('admin.users', ['pageTitle' => 'Users'], 'layouts.app');
        exit;
    }

    public function forms(Request $request, array $params): void
    {
        $client = new EdobaseClient();
        $forms = $client->listDocuments('forms', ['limit' => 50, 'sort' => 'created_at', 'order' => 'desc']);

        echo view('admin.forms', [
            'pageTitle' => 'All Forms',
            'forms'     => $forms['data'] ?? [],
            'meta'      => $forms['meta'] ?? [],
        ], 'layouts.app');
        exit;
    }

    public function logs(Request $request, array $params): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        $logFile = $logDir . '/app-' . date('Y-m-d') . '.log';
        $logs = file_exists($logFile) ? array_slice(array_reverse(file($logFile)), 0, 100) : [];

        echo view('admin.logs', [
            'pageTitle' => 'Application Logs',
            'logs'      => $logs,
        ], 'layouts.app');
        exit;
    }
}
