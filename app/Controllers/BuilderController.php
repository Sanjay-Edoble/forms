<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FormService;

class BuilderController
{
    private FormService $formService;

    public function __construct()
    {
        $this->formService = new FormService();
    }

    public function index(Request $request, array $params): void
    {
        $form = $this->formService->getById($params['id']);
        if (!$form) {
            flash('error', 'Form not found.');
            redirect('/forms');
        }

        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to edit this form.');
            redirect('/forms');
        }

        // Parse JSON fields
        $schema = json_decode($form['schema'] ?? '{}', true);
        $settings = json_decode($form['settings'] ?? '{}', true);
        $theme = json_decode($form['theme'] ?? '{}', true);

        echo view('builder.index', [
            'pageTitle' => 'Edit: ' . ($form['title'] ?? 'Untitled'),
            'form'      => $form,
            'schema'    => $schema,
            'settings'  => $settings,
            'theme'     => $theme,
        ]);
        exit;
    }

    /**
     * Autosave form schema (AJAX).
     */
    public function save(Request $request, array $params): void
    {
        $schema = $request->json();
        if (empty($schema)) {
            Response::json(['success' => false, 'message' => 'No schema data provided'], 400);
        }

        $result = $this->formService->updateSchema($params['id'], $schema);
        Response::json($result);
    }

    /**
     * Save form title/description (AJAX).
     */
    public function saveMeta(Request $request, array $params): void
    {
        $title = $request->input('title', 'Untitled Form');
        $description = $request->input('description', '');

        $result = $this->formService->updateMeta($params['id'], $title, $description);
        Response::json($result);
    }

    /**
     * Save form settings (AJAX).
     */
    public function saveSettings(Request $request, array $params): void
    {
        $settings = $request->json();
        $result = $this->formService->updateSettings($params['id'], $settings);
        Response::json($result);
    }

    /**
     * Save form theme (AJAX).
     */
    public function saveTheme(Request $request, array $params): void
    {
        $theme = $request->json();
        $result = $this->formService->updateTheme($params['id'], $theme);
        Response::json($result);
    }
}
