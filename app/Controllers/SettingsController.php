<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\FormService;

class SettingsController
{
    public function formSettings(Request $request, array $params): void
    {
        $formService = new FormService();
        $form = $formService->getById($params['id']);
        if (!$form) { flash('error', 'Form not found.'); redirect('/forms'); }

        $settings = json_decode($form['settings'] ?? '{}', true);
        $theme = json_decode($form['theme'] ?? '{}', true);

        echo view('settings.form', [
            'pageTitle' => 'Settings: ' . ($form['title'] ?? 'Untitled'),
            'form'      => $form,
            'settings'  => $settings,
            'theme'     => $theme,
        ], 'layouts.app');
        exit;
    }

    public function updateFormSettings(Request $request, array $params): void
    {
        $formService = new FormService();
        $settings = [
            'collect_email'        => (bool) $request->input('collect_email'),
            'allow_multiple'       => (bool) $request->input('allow_multiple'),
            'show_progress'        => (bool) $request->input('show_progress'),
            'require_login'        => (bool) $request->input('require_login'),
            'shuffle_questions'    => (bool) $request->input('shuffle_questions'),
            'confirmation_message' => $request->input('confirmation_message', 'Your response has been recorded.'),
            'notify_on_submit'     => (bool) $request->input('notify_on_submit'),
            'notify_email'         => $request->input('notify_email', ''),
            'start_date'           => $request->input('start_date') ?: null,
            'end_date'             => $request->input('end_date') ?: null,
        ];

        $formService->updateSettings($params['id'], $settings);
        flash('success', 'Settings updated successfully.');
        redirect("/forms/{$params['id']}/settings");
    }

    public function accountSettings(Request $request, array $params): void
    {
        echo view('settings.account', [
            'pageTitle' => 'Account Settings',
            'user'      => current_user(),
        ], 'layouts.app');
        exit;
    }

    public function updateAccountSettings(Request $request, array $params): void
    {
        flash('success', 'Settings saved.');
        redirect('/settings');
    }
}
