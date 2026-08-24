<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\FormService;

class PreviewController
{
    public function index(Request $request, array $params): void
    {
        $formService = new FormService();
        $form = $formService->getById($params['id']);
        if (!$form) {
            flash('error', 'Form not found.');
            redirect('/forms');
        }

        $schema = json_decode($form['schema'] ?? '{}', true);
        $settings = json_decode($form['settings'] ?? '{}', true);
        $theme = json_decode($form['theme'] ?? '{}', true);

        echo view('forms.preview', [
            'form'     => $form,
            'schema'   => $schema,
            'settings' => $settings,
            'theme'    => $theme,
        ]);
        exit;
    }
}
