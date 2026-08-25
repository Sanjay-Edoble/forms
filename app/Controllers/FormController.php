<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FormService;

class FormController
{
    private FormService $formService;

    public function __construct()
    {
        $this->formService = new FormService();
    }

    public function index(Request $request, array $params): void
    {
        $page = (int) $request->query('page', 1);
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $queryParams = ['page' => $page, 'limit' => 12];
        if ($search) $queryParams['search'] = $search;
        if ($status) $queryParams['filter[status]'] = $status;

        $result = $this->formService->listForUser($queryParams);

        echo view('forms.index', [
            'pageTitle' => 'My Forms',
            'forms'     => $result['data'] ?? [],
            'meta'      => $result['meta'] ?? [],
            'search'    => $search,
            'status'    => $status,
        ], 'layouts.app');
        exit;
    }

    public function create(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to create forms.');
            redirect('/forms');
            return;
        }

        echo view('forms.create', [
            'pageTitle' => 'Create Form',
        ], 'layouts.app');
        exit;
    }

    public function store(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to create forms.');
            redirect('/forms');
            return;
        }

        $title = trim($request->input('title', 'Untitled Form'));
        $templateId = $request->input('template_id', '');

        if ($templateId) {
            // Create from template
            $template = $this->formService->getById($templateId);
            if ($template) {
                $result = $this->formService->createFromTemplate($template);
            } else {
                flash('error', 'Template not found.');
                redirect('/forms/create');
            }
        } else {
            $result = $this->formService->create($title);
        }

        if ($result['success'] ?? false) {
            $formId = $result['data']['id'] ?? $result['data']['_id'] ?? '';
            flash('success', 'Form created successfully!');
            redirect("/forms/{$formId}/edit");
        }

        flash('error', $result['message'] ?? 'Failed to create form.');
        redirect('/forms/create');
    }

    public function duplicate(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to duplicate forms.');
            redirect('/forms');
            return;
        }

        $result = $this->formService->duplicate($params['id']);

        if ($result['success'] ?? false) {
            $newId = $result['data']['id'] ?? $result['data']['_id'] ?? '';
            flash('success', 'Form duplicated successfully!');
            redirect("/forms/{$newId}/edit");
        }

        flash('error', $result['message'] ?? 'Failed to duplicate form.');
        redirect('/forms');
    }

    public function delete(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to delete forms.');
            redirect('/forms');
            return;
        }

        $this->formService->trash($params['id']);
        flash('success', 'Form moved to trash.');
        redirect('/forms');
    }

    public function updateStatus(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to update form status.');
            redirect('/forms');
            return;
        }

        $status = $request->input('status', '');
        $result = $this->formService->setStatus($params['id'], $status);

        if ($request->isAjax()) {
            Response::json($result);
        }

        flash($result['success'] ? 'success' : 'error',
            $result['success'] ? 'Form status updated.' : ($result['message'] ?? 'Failed to update status.'));
        redirect('/forms');
    }

    public function publish(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
            return;
        }

        $result = $this->formService->setStatus($params['id'], 'published');
        Response::json($result);
    }

    public function unpublish(Request $request, array $params): void
    {
        if (current_user_role() === 'viewer') {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
            return;
        }

        $result = $this->formService->setStatus($params['id'], 'draft');
        Response::json($result);
    }

    public function stats(Request $request, array $params): void
    {
        $form = $this->formService->getById($params['id']);
        if (!$form) {
            Response::json(['success' => false, 'message' => 'Form not found'], 404);
        }
        Response::json(['success' => true, 'data' => [
            'response_count' => $form['response_count'] ?? 0,
            'status'         => $form['status'] ?? 'draft',
        ]]);
    }
}
