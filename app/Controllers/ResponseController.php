<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FormService;
use App\Services\ResponseService;

class ResponseController
{
    private FormService $formService;
    private ResponseService $responseService;

    public function __construct()
    {
        $this->formService = new FormService();
        $this->responseService = new ResponseService();
    }

    /**
     * Live Data Sheet page.
     */
    public function index(Request $request, array $params): void
    {
        $form = $this->formService->getById($params['id']);
        if (!$form) {
            flash('error', 'Form not found.');
            redirect('/forms');
        }

        $schema = json_decode($form['schema'] ?? '{}', true);

        echo view('responses.index', [
            'pageTitle' => 'Responses: ' . ($form['title'] ?? 'Untitled'),
            'form'      => $form,
            'schema'    => $schema,
        ], 'layouts.app');
        exit;
    }

    /**
     * Show single response detail.
     */
    public function show(Request $request, array $params): void
    {
        $form = $this->formService->getById($params['id']);
        $response = $this->responseService->getById($params['id'], $params['responseId']);

        if (!$form || !$response) {
            flash('error', 'Response not found.');
            redirect("/forms/{$params['id']}/responses");
        }

        $schema = json_decode($form['schema'] ?? '{}', true);
        $answers = json_decode($response['answers'] ?? '{}', true);

        echo view('responses.detail', [
            'pageTitle' => 'Response Detail',
            'form'      => $form,
            'response'  => $response,
            'schema'    => $schema,
            'answers'   => $answers,
        ], 'layouts.app');
        exit;
    }

    /**
     * Update a response.
     */
    public function update(Request $request, array $params): void
    {
        $answers = $request->input('answers', []);
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }

        $result = $this->responseService->update($params['id'], $params['responseId'], $answers);

        if ($request->isAjax()) {
            Response::json($result);
        }

        flash($result['success'] ? 'success' : 'error',
            $result['success'] ? 'Response updated.' : 'Failed to update response.');
        redirect("/forms/{$params['id']}/responses/{$params['responseId']}");
    }

    /**
     * Delete a response.
     */
    public function delete(Request $request, array $params): void
    {
        $result = $this->responseService->delete($params['id'], $params['responseId']);

        if ($request->isAjax()) {
            Response::json($result);
        }

        flash('success', 'Response deleted.');
        redirect("/forms/{$params['id']}/responses");
    }

    /**
     * Bulk delete responses.
     */
    public function bulkDelete(Request $request, array $params): void
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?? [];
        }

        $result = $this->responseService->bulkDelete($params['id'], $ids);
        Response::json($result);
    }

    // ─── API Endpoints (AJAX) ──────────────────────────────────

    /**
     * List responses (JSON for Live Data Sheet).
     */
    public function apiList(Request $request, array $params): void
    {
        $queryParams = [
            'page'   => (int) $request->query('page', 1),
            'limit'  => (int) $request->query('limit', 25),
            'sort'   => $request->query('sort', 'created_at'),
            'order'  => $request->query('order', 'desc'),
        ];

        if ($search = $request->query('search')) {
            $queryParams['search'] = $search;
        }

        // Apply filters
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter[')) {
                $queryParams[$key] = $value;
            }
        }

        $result = $this->responseService->list($params['id'], $queryParams);
        Response::json($result);
    }

    /**
     * Poll for new responses (Live Data Sheet).
     */
    public function poll(Request $request, array $params): void
    {
        $since = $request->query('since', '');
        if (empty($since)) {
            Response::json(['success' => true, 'data' => [], 'new_count' => 0]);
        }

        $result = $this->responseService->getNewResponses($params['id'], $since);
        $newResponses = $result['data'] ?? [];

        Response::json([
            'success'   => true,
            'data'      => $newResponses,
            'new_count' => count($newResponses),
        ]);
    }

    /**
     * Get single response (JSON).
     */
    public function apiShow(Request $request, array $params): void
    {
        $response = $this->responseService->getById($params['id'], $params['responseId']);
        if (!$response) {
            Response::json(['success' => false, 'message' => 'Response not found'], 404);
        }
        Response::json(['success' => true, 'data' => $response]);
    }
}
