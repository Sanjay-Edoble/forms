<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\FormService;

class TrashController
{
    private FormService $formService;

    public function __construct()
    {
        $this->formService = new FormService();
    }

    public function index(Request $request, array $params): void
    {
        $result = $this->formService->listTrashed();

        echo view('trash.index', [
            'pageTitle' => 'Trash',
            'forms'     => $result['data'] ?? [],
            'meta'      => $result['meta'] ?? [],
        ], 'layouts.app');
        exit;
    }

    public function restore(Request $request, array $params): void
    {
        $this->formService->restore($params['id']);
        flash('success', 'Form restored successfully.');
        redirect('/trash');
    }

    public function permanentDelete(Request $request, array $params): void
    {
        $this->formService->permanentDelete($params['id']);
        flash('success', 'Form permanently deleted.');
        redirect('/trash');
    }
}
