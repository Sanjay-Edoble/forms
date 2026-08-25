<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Core\Response;

/**
 * Ensures user is authenticated. Redirects to login if not.
 */
class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Session::has('user_token') || !Session::has('user')) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            }
            Session::setFlash('error', 'Please log in to continue.');
            Session::set('intended_url', $request->path());
            redirect('/login');
        }

        // Ensure workspaces are loaded for backwards compatibility with active sessions
        if (!Session::has('workspaces') || !Session::has('current_workspace_id')) {
            $workspaceService = new \App\Services\WorkspaceService();
            $workspaces = $workspaceService->getUserWorkspaces(Session::get('user')['id']);
            if (!empty($workspaces)) {
                Session::set('workspaces', $workspaces);
                Session::set('current_workspace_id', $workspaces[0]['id']);
            }
        }

        return true;
    }
}
