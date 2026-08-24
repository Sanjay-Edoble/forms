<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\FormService;

class ShareController
{
    public function index(Request $request, array $params): void
    {
        $formService = new FormService();
        $form = $formService->getById($params['id']);
        if (!$form) { flash('error', 'Form not found.'); redirect('/forms'); }

        $formUrl = url("/f/{$params['id']}");
        $embedCode = '<iframe src="' . url("/embed/{$params['id']}") . '" width="100%" height="600" frameborder="0" style="border:0;border-radius:8px;" allowfullscreen></iframe>';

        echo view('share.index', [
            'pageTitle'  => 'Share: ' . ($form['title'] ?? 'Untitled'),
            'form'       => $form,
            'formUrl'    => $formUrl,
            'embedCode'  => $embedCode,
        ], 'layouts.app');
        exit;
    }
}
