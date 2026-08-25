<?php

namespace App\Services;

use App\Core\Session;
use App\Helpers\Logger;

class WorkspaceService
{
    private EdobaseClient $client;
    private FormService $formService;

    public function __construct()
    {
        $this->client = new EdobaseClient();
        $this->formService = new FormService();
    }

    public function getUserWorkspaces(string $userId): array
    {
        // Get all member records for this user
        $memberships = $this->client->listDocuments('workspace_members', [
            'filter[user_id]' => $userId
        ]);

        if (!($memberships['success'] ?? false) || empty($memberships['data'])) {
            // Auto-create personal workspace if none exist
            $workspace = $this->createWorkspace("Personal Workspace", $userId);
            if ($workspace) {
                // Auto-migrate old forms
                $this->migrateOldForms($userId, $workspace['id']);
                return [$workspace];
            }
            return [];
        }

        foreach ($memberships['data'] as $mem) {
            $wId = $mem['workspace_id'];
            $w = $this->client->getDocument('workspaces', $wId);
            if ($w['success'] ?? false) {
                $workspace = $w['data'];
                $workspace['my_role'] = $mem['role'];
                $workspaces[] = $workspace;
            }
        }

        return $workspaces;
    }

    public function createWorkspace(string $name, string $ownerId): ?array
    {
        $res = $this->client->createDocument('workspaces', [
            'name' => $name,
            'owner_id' => $ownerId,
            'created_at' => date('c')
        ]);

        if ($res['success'] ?? false) {
            $workspace = $res['data'];
            // Add owner as admin member
            $this->client->createDocument('workspace_members', [
                'workspace_id' => $workspace['id'],
                'user_id' => $ownerId,
                'role' => 'admin',
                'created_at' => date('c')
            ]);
            $workspace['my_role'] = 'admin';
            return $workspace;
        }
        return null;
    }

    public function getWorkspaceMembers(string $workspaceId): array
    {
        $res = $this->client->listDocuments('workspace_members', [
            'filter[workspace_id]' => $workspaceId
        ]);
        return ($res['success'] ?? false) ? $res['data'] : [];
    }

    public function inviteUser(string $workspaceId, string $email, string $role = 'editor'): bool
    {
        // 1. Find user by email (we might need a custom endpoint or search users if Edobase allows)
        // Since we don't have a direct user search in EdobaseClient, we might just store pending invites
        // For now, let's assume we can search users if we have admin rights? No, EdobaseClient doesn't have listUsers.
        // We will just store the invite in 'workspace_invites'.
        $res = $this->client->createDocument('workspace_invites', [
            'workspace_id' => $workspaceId,
            'email' => strtolower(trim($email)),
            'role' => $role,
            'created_at' => date('c')
        ]);
        return $res['success'] ?? false;
    }

    public function processPendingInvites(string $email, string $userId): void
    {
        $invites = $this->client->listDocuments('workspace_invites', [
            'filter[email]' => strtolower(trim($email))
        ]);

        if (!empty($invites['data'])) {
            foreach ($invites['data'] as $invite) {
                // Add to workspace
                $this->client->createDocument('workspace_members', [
                    'workspace_id' => $invite['workspace_id'],
                    'user_id' => $userId,
                    'role' => $invite['role'],
                    'created_at' => date('c')
                ]);
                // Delete invite
                $this->client->deleteDocument('workspace_invites', $invite['id']);
            }
        }
    }

    private function migrateOldForms(string $userId, string $workspaceId): void
    {
        $forms = $this->client->listDocuments('forms', [
            'filter[owner_id]' => $userId,
            'limit' => 100
        ]);
        
        if ($forms['success'] ?? false) {
            foreach ($forms['data'] as $form) {
                if (empty($form['workspace_id'])) {
                    $this->client->patchDocument('forms', $form['id'], [
                        'workspace_id' => $workspaceId
                    ]);
                }
            }
        }
    }
}
