<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FormService;
use App\Services\ResponseService;

class AnalyticsController
{
    public function index(Request $request, array $params): void
    {
        $formService = new FormService();
        $responseService = new ResponseService();

        $form = $formService->getById($params['id']);
        if (!$form) {
            flash('error', 'Form not found.');
            redirect('/forms');
        }

        $schema = json_decode($form['schema'] ?? '{}', true);
        $stats = $responseService->getStats($params['id']);

        // Get responses for analytics aggregation (up to 500 for charts)
        $responses = $responseService->getAllForExport($params['id']);
        $questionsData = $this->aggregateQuestionData($schema, $responses);

        echo view('forms.analytics', [
            'pageTitle'     => 'Analytics: ' . ($form['title'] ?? 'Untitled'),
            'form'          => $form,
            'schema'        => $schema,
            'stats'         => $stats,
            'questionsData' => $questionsData,
        ], 'layouts.app');
        exit;
    }

    /**
     * Aggregate response data per question for chart generation.
     */
    private function aggregateQuestionData(array $schema, array $responses): array
    {
        $questions = $schema['questions'] ?? [];
        $chartableTypes = ['multiple_choice', 'checkboxes', 'dropdown', 'rating', 'linear_scale'];
        $result = [];

        foreach ($questions as $q) {
            $qId = $q['id'] ?? '';
            $type = $q['type'] ?? '';

            if (!in_array($type, $chartableTypes)) continue;

            $distribution = [];

            foreach ($responses as $resp) {
                $answers = json_decode($resp['answers'] ?? '{}', true);
                $value = $answers[$qId] ?? null;

                if ($value === null || $value === '') continue;

                if ($type === 'checkboxes' && is_array($value)) {
                    foreach ($value as $v) {
                        $distribution[$v] = ($distribution[$v] ?? 0) + 1;
                    }
                } else {
                    $key = is_array($value) ? implode(', ', $value) : (string)$value;
                    $distribution[$key] = ($distribution[$key] ?? 0) + 1;
                }
            }

            $result[] = [
                'id'           => $qId,
                'title'        => $q['title'] ?? 'Question',
                'type'         => $type,
                'distribution' => $distribution,
                'total'        => array_sum($distribution),
            ];
        }

        return $result;
    }
}
