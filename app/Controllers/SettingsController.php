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
        if (!$form) { flash('error', 'Form not found.'); redirect('/forms'); return; }

        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to view settings.');
            redirect('/forms');
            return;
        }

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
        if (current_user_role() === 'viewer') {
            flash('error', 'You do not have permission to edit settings.');
            redirect('/forms');
            return;
        }

        $formService = new FormService();
        $settings = [
            'require_email'           => (bool) $request->input('require_email'),
            'limit_one_response'      => (bool) $request->input('limit_one_response'),
            'verify_email_magic_link' => (bool) $request->input('verify_email_magic_link'),
            'presentation_mode'       => (bool) $request->input('presentation_mode'),
            'show_progress'           => (bool) $request->input('show_progress'),
            'shuffle_questions'       => (bool) $request->input('shuffle_questions'),
            'confirmation_message'    => $request->input('confirmation_message', 'Your response has been recorded.'),
            'webhook_url'             => $request->input('webhook_url', ''),
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

    public function workspaces(Request $request, array $params): void
    {
        echo view('settings.workspaces', [
            'pageTitle'  => 'Workspaces',
            'workspaces' => \App\Core\Session::get('workspaces') ?? [],
        ], 'layouts.app');
        exit;
    }

    public function createWorkspace(Request $request, array $params): void
    {
        $name = $request->input('workspace_name');
        if (!$name) {
            flash('error', 'Workspace name is required');
            redirect('/settings/workspaces');
            return;
        }

        $workspaceService = new \App\Services\WorkspaceService();
        $ws = $workspaceService->createWorkspace($name, current_user()['id']);
        if ($ws) {
            flash('success', 'Workspace created');
            
            // Refresh workspaces in session
            $workspaces = $workspaceService->getUserWorkspaces(current_user()['id']);
            \App\Core\Session::set('workspaces', $workspaces);
            \App\Core\Session::set('current_workspace_id', $ws['id']);
        } else {
            flash('error', 'Failed to create workspace');
        }
        redirect('/settings/workspaces');
    }

    public function switchWorkspace(Request $request, array $params): void
    {
        $workspaceId = $params['id'];
        $workspaces = \App\Core\Session::get('workspaces') ?? [];
        
        $found = null;
        foreach ($workspaces as $w) {
            if ($w['id'] === $workspaceId) {
                $found = $w;
                break;
            }
        }
        
        if ($found) {
            \App\Core\Session::set('current_workspace_id', $workspaceId);
            \App\Core\Session::set('current_workspace_role', $found['my_role'] ?? 'viewer');
            flash('success', 'Switched workspace.');
        } else {
            flash('error', 'Workspace not found.');
        }
        redirect('/settings/workspaces');
    }

    public function inviteMember(Request $request, array $params): void
    {
        $workspaceId = $params['id'];
        $email = $request->input('email');
        $role = $request->input('role', 'editor');

        if (!$email) {
            flash('error', 'Email is required');
            redirect('/settings/workspaces');
            return;
        }

        $workspaceService = new \App\Services\WorkspaceService();
        $success = $workspaceService->inviteUser($workspaceId, $email, $role);
        
        if ($success) {
            flash('success', "Invited {$email} to workspace as {$role}.");
        } else {
            flash('error', 'Failed to send invite.');
        }

        redirect('/settings/workspaces');
    }
}
