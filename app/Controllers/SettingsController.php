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

        echo view('forms.settings', [
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
            'require_email'        => (bool) $request->input('require_email'),
            'limit_one_response'   => (bool) $request->input('limit_one_response'),
            'show_progress'        => (bool) $request->input('show_progress'),
            'shuffle_questions'    => (bool) $request->input('shuffle_questions'),
            'confirmation_message' => $request->input('confirmation_message', 'Your response has been recorded.'),
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
