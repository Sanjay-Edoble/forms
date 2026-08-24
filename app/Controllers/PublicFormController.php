<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FormService;
use App\Services\ResponseService;
use App\Services\MailService;
use App\Validators\ResponseValidator;
use App\Helpers\Logger;

class PublicFormController
{
    private FormService $formService;
    private ResponseService $responseService;

    public function __construct()
    {
        $this->formService = new FormService();
        $this->responseService = new ResponseService();
    }

    /**
     * Show public form.
     */
    public function show(Request $request, array $params): void
    {
        $form = $this->formService->getById($params['id']);

        if (!$form || ($form['status'] ?? '') !== 'published') {
            echo view('forms.closed', [
                'message' => 'This form is not currently accepting responses.',
            ], 'layouts.public');
            exit;
        }

        $settings = json_decode($form['settings'] ?? '{}', true);
        $now = date('Y-m-d H:i:s');
        if (!empty($settings['start_date']) && $now < $settings['start_date']) {
            echo view('forms.closed', ['message' => 'This form is not yet open for responses.'], 'layouts.public');
            exit;
        }
        if (!empty($settings['end_date']) && $now > $settings['end_date']) {
            echo view('forms.closed', ['message' => 'This form is no longer accepting responses.'], 'layouts.public');
            exit;
        }

        // Access Control: Require Email
        if (!empty($settings['require_email'])) {
            $sessionKey = 'form_' . $params['id'] . '_email';
            $respondentEmail = $_SESSION[$sessionKey] ?? null;

            if (!$respondentEmail) {
                echo view('forms.gate', ['form' => $form], 'layouts.public');
                exit;
            }

            // If limited to 1 response, check if this email already submitted
            if (!empty($settings['limit_one_response'])) {
                $existing = $this->responseService->list($params['id'], [
                    'filter[email]' => $respondentEmail,
                    'limit' => 1,
                ]);
                if (!empty($existing['data'])) {
                    echo view('forms.closed', ['message' => 'You have already responded to this form. Multiple submissions are not allowed.'], 'layouts.public');
                    exit;
                }
            }
        }

        $schema = json_decode($form['schema'] ?? '{}', true);
        $theme = json_decode($form['theme'] ?? '{}', true);

        echo view('forms.public', [
            'form'     => $form,
            'schema'   => $schema,
            'settings' => $settings,
            'theme'    => $theme,
        ], 'layouts.public');
        exit;
    }

    /**
     * Handle email gate submission.
     */
    public function gate(Request $request, array $params): void
    {
        $email = $request->input('respondent_email');
        if ($email) {
            $_SESSION['form_' . $params['id'] . '_email'] = filter_var($email, FILTER_SANITIZE_EMAIL);
        }
        redirect("/f/{$params['id']}");
    }

    /**
     * Handle form submission.
     */
    public function submit(Request $request, array $params): void
    {
        $formId = $params['id'];
        $form = $this->formService->getById($formId);

        if (!$form || ($form['status'] ?? '') !== 'published') {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'This form is not accepting responses.'], 403);
            }
            flash('error', 'This form is not accepting responses.');
            redirect("/f/{$formId}");
        }

        $settings = json_decode($form['settings'] ?? '{}', true);
        $schema = json_decode($form['schema'] ?? '{}', true);

        // Collect answers
        $answers = $request->input('answers', []);
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }

        // Validate
        $validator = new ResponseValidator();
        $errors = $validator->validate($schema, $answers);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'], 422);
            }
            flash('error', 'Please fix the errors below.');
            flash('validation_errors', $errors);
            redirect("/f/{$formId}");
        }

        // Check for duplicate submissions
        $sessionEmail = $_SESSION['form_' . $formId . '_email'] ?? null;
        
        if (!empty($settings['limit_one_response'])) {
            if (!$sessionEmail) {
                if ($request->isAjax()) Response::json(['success' => false, 'message' => 'Email is required to submit this form.'], 403);
                flash('error', 'Email is required to submit this form.');
                redirect("/f/{$formId}");
            }
            
            $existing = $this->responseService->list($formId, [
                'filter[email]' => $sessionEmail,
                'limit' => 1,
            ]);
            if (!empty($existing['data'])) {
                if ($request->isAjax()) Response::json(['success' => false, 'message' => 'You have already submitted a response.'], 409);
                flash('error', 'You have already submitted a response to this form.');
                redirect("/f/{$formId}");
            }
        } elseif (!($settings['allow_multiple'] ?? true)) {
            // Old IP-based check as fallback if limit_one_response is not strictly used via email
            $ipHash = hash('sha256', $request->ip() . ':' . $formId);
            $existing = $this->responseService->list($formId, [
                'filter[ip_hash]' => $ipHash,
                'limit' => 1,
            ]);
            if (!empty($existing['data'])) {
                if ($request->isAjax()) Response::json(['success' => false, 'message' => 'You have already submitted a response.'], 409);
                flash('error', 'You have already submitted a response to this form.');
                redirect("/f/{$formId}");
            }
        }

        $ipHash = hash('sha256', $request->ip() . ':' . $formId);
        $email = $sessionEmail ?? $answers['_email'] ?? ($settings['collect_email'] ? ($answers['email'] ?? null) : null);

        $result = $this->responseService->submit(
            $formId,
            $answers,
            $form['version'] ?? 1,
            $email,
            $ipHash
        );

        if ($result['success'] ?? false) {
            // Send notifications
            $mailService = new MailService();
            $mailService->sendResponseNotification($form, $answers);

            if ($email && ($settings['collect_email'] ?? false)) {
                $mailService->sendRespondentConfirmation($email, $form);
            }

            if ($request->isAjax()) {
                Response::json([
                    'success' => true,
                    'message' => $settings['confirmation_message'] ?? 'Your response has been recorded.',
                ]);
            }

            flash('form_submitted', true);
            flash('confirmation_message', $settings['confirmation_message'] ?? 'Your response has been recorded.');
            redirect("/f/{$formId}");
        }

        if ($request->isAjax()) {
            Response::json(['success' => false, 'message' => 'Failed to submit response. Please try again.'], 500);
        }

        flash('error', 'Failed to submit response. Please try again.');
        redirect("/f/{$formId}");
    }

    /**
     * Embed view.
     */
    public function embed(Request $request, array $params): void
    {
        $form = $this->formService->getById($params['id']);

        if (!$form || ($form['status'] ?? '') !== 'published') {
            echo '<p style="padding:20px;font-family:sans-serif;color:#666;">This form is not available.</p>';
            exit;
        }

        $schema = json_decode($form['schema'] ?? '{}', true);
        $settings = json_decode($form['settings'] ?? '{}', true);
        $theme = json_decode($form['theme'] ?? '{}', true);

        echo view('forms.embed', [
            'form'     => $form,
            'schema'   => $schema,
            'settings' => $settings,
            'theme'    => $theme,
        ]);
        exit;
    }
}
