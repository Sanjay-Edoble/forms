<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\FormService;

class DashboardController
{
    private FormService $formService;

    public function __construct()
    {
        $this->formService = new FormService();
    }

    public function index(Request $request, array $params): void
    {
        $stats = $this->formService->getDashboardStats();

        echo view('dashboard.index', [
            'pageTitle'       => 'Dashboard',
            'totalForms'      => $stats['total_forms'],
            'publishedForms'  => $stats['published_forms'],
            'totalResponses'  => $stats['total_responses'],
            'recentForms'     => $stats['recent_forms'],
        ], 'layouts.app');
        exit;
    }
}
