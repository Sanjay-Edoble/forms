<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;

class WorkspaceController
{
    public function switchWorkspace(Request $request, array $params): void
    {
        $workspaceId = $request->input('workspace_id');
        $workspaces = Session::get('workspaces') ?? [];

        $found = null;
        foreach ($workspaces as $w) {
            if ($w['id'] === $workspaceId) {
                $found = $w;
                break;
            }
        }

        if ($found) {
            Session::set('current_workspace_id', $workspaceId);
            Session::set('current_workspace_role', $found['my_role'] ?? 'viewer');
        }

        redirect('/dashboard');
    }
}
