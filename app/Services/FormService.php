<?php

namespace App\Services;

use App\Core\Session;
use App\Helpers\Logger;

/**
 * Form business logic — CRUD, duplication, versioning, templates.
 */
class FormService
{
    private EdobaseClient $client;

    public function __construct()
    {
        $this->client = new EdobaseClient();
    }

    /**
     * Create a new blank form.
     */
    public function create(string $title, string $description = ''): array
    {
        $user = Session::get('user');
        $workspaceId = Session::get('current_workspace_id');
        if (!$workspaceId) {
            // Fallback for missing workspace
            $workspaceId = $user['id'];
        }
        $formId = generate_id('frm_');

        $form = [
            '_id'         => $formId,
            'title'       => $title,
            'description' => $description,
            'status'      => 'draft',
            'version'     => 1,
            'owner_id'    => $user['id'], // Keep owner_id for tracking who created it
            'workspace_id'=> $workspaceId,
            'owner_name'  => $user['display_name'] ?? $user['email'],
            'schema'      => json_encode([
                'questions' => [],
                'sections'  => [
                    ['id' => generate_id('sec_'), 'title' => 'Untitled Section', 'order' => 0]
                ],
            ]),
            'settings'    => json_encode([
                'collect_email'    => true,
                'require_email'    => true,
                'verify_email_magic_link' => true,
                'limit_one_response' => false,
                'allow_multiple'   => true,
                'show_progress'    => false,
                'require_login'    => false,
                'shuffle_questions' => false,
                'confirmation_message' => 'Your response has been recorded.',
                'notify_on_submit' => false,
                'notify_email'     => $user['email'],
                'webhook_url'      => '',
                'start_date'       => null,
                'end_date'         => null,
            ]),
            'theme'       => json_encode([
                'preset'        => 'edoble',
                'primary_color' => '#6366f1',
                'bg_color'      => '#ffffff',
                'font'          => 'Inter',
                'border_radius' => 8,
                'header_image'  => null,
                'logo'          => null,
            ]),
            'response_count' => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        $result = $this->client->createDocument('forms', $form);

        if ($result['success'] ?? false) {
            Logger::info('Form created', ['form_id' => $formId, 'title' => $title]);
        }

        return $result;
    }

    /**
     * Create a form from a template.
     */
    public function createFromTemplate(array $template): array
    {
        $user = Session::get('user');
        $workspaceId = Session::get('current_workspace_id') ?? $user['id'];
        $formId = generate_id('frm_');

        $form = [
            '_id'            => $formId,
            'title'          => $template['title'] ?? 'Untitled Form',
            'description'    => $template['description'] ?? '',
            'status'         => 'draft',
            'version'        => 1,
            'owner_id'       => $user['id'],
            'workspace_id'   => $workspaceId,
            'owner_name'     => $user['display_name'] ?? $user['email'],
            'schema'         => $template['schema'] ?? '{"questions":[],"sections":[]}',
            'settings'       => $template['settings'] ?? '{}',
            'theme'          => $template['theme'] ?? '{}',
            'response_count' => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        return $this->client->createDocument('forms', $form);
    }

    /**
     * Duplicate a form.
     */
    public function duplicate(string $formId): array
    {
        $original = $this->getById($formId);
        if (!$original) {
            return ['success' => false, 'message' => 'Form not found'];
        }

        $form = $original;
        $form['_id'] = generate_id('frm_');
        $form['title'] = $form['title'] . ' (Copy)';
        $form['status'] = 'draft';
        $form['version'] = 1;
        $form['response_count'] = 0;
        $form['created_at'] = date('Y-m-d H:i:s');
        $form['updated_at'] = date('Y-m-d H:i:s');
        unset($form['id']); // Remove the original Edobase ID

        Logger::info('Form duplicated', ['original' => $formId, 'new' => $form['_id']]);
        return $this->client->createDocument('forms', $form);
    }

    /**
     * Get a form by ID.
     */
    public function getById(string $id): ?array
    {
        $result = $this->client->getDocument('forms', $id);
        if ($result['success'] ?? false) {
            return $result['data'];
        }
        return null;
    }

    /**
     * List forms for current user.
     */
    public function listForUser(array $params = []): array
    {
        $user = Session::get('user');
        $workspaceId = Session::get('current_workspace_id') ?? $user['id'];
        $defaults = [
            'filter[workspace_id]' => $workspaceId,
            'sort'             => 'updated_at',
            'order'            => 'desc',
            'limit'            => 20,
            'page'             => 1,
        ];

        // Exclude deleted (trashed) forms unless explicitly requested
        if (!isset($params['include_deleted'])) {
            $defaults['filter[status][ne]'] = 'deleted';
        }
        unset($params['include_deleted']);

        $merged = array_merge($defaults, $params);
        return $this->client->listDocuments('forms', $merged);
    }

    /**
     * List trashed forms.
     */
    public function listTrashed(array $params = []): array
    {
        $user = Session::get('user');
        $workspaceId = Session::get('current_workspace_id') ?? $user['id'];
        $defaults = [
            'filter[workspace_id]' => $workspaceId,
            'filter[status]'   => 'deleted',
            'sort'             => 'updated_at',
            'order'            => 'desc',
            'limit'            => 20,
        ];
        return $this->client->listDocuments('forms', array_merge($defaults, $params));
    }

    /**
     * Update the form schema (autosave from builder).
     */
    public function updateSchema(string $formId, array $schema): array
    {
        return $this->client->patchDocument('forms', $formId, [
            'schema'     => json_encode($schema),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update form settings.
     */
    public function updateSettings(string $formId, array $settings): array
    {
        return $this->client->patchDocument('forms', $formId, [
            'settings'   => json_encode($settings),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update form theme.
     */
    public function updateTheme(string $formId, array $theme): array
    {
        return $this->client->patchDocument('forms', $formId, [
            'theme'      => json_encode($theme),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update form title and description.
     */
    public function updateMeta(string $formId, string $title, string $description = ''): array
    {
        return $this->client->patchDocument('forms', $formId, [
            'title'       => $title,
            'description' => $description,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Change form status.
     */
    public function setStatus(string $formId, string $status): array
    {
        $validStatuses = ['draft', 'published', 'paused', 'closed', 'archived', 'deleted'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $data = [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Bump version when publishing
        if ($status === 'published') {
            $form = $this->getById($formId);
            if ($form) {
                $data['version'] = ($form['version'] ?? 0) + 1;
            }
        }

        Logger::info('Form status changed', ['form_id' => $formId, 'status' => $status]);
        return $this->client->patchDocument('forms', $formId, $data);
    }

    /**
     * Soft delete (move to trash).
     */
    public function trash(string $formId): array
    {
        return $this->setStatus($formId, 'deleted');
    }

    /**
     * Restore from trash.
     */
    public function restore(string $formId): array
    {
        return $this->setStatus($formId, 'draft');
    }

    /**
     * Permanently delete a form and its response collection.
     */
    public function permanentDelete(string $formId): array
    {
        // Delete the response collection
        $this->client->deleteCollection("responses_{$formId}");

        // Delete the form document
        $result = $this->client->deleteDocument('forms', $formId);
        Logger::info('Form permanently deleted', ['form_id' => $formId]);
        return $result;
    }

    /**
     * Empty the trash for the current workspace.
     */
    public function emptyTrash(): void
    {
        $trashed = $this->listTrashed(['limit' => 1000]);
        if (!empty($trashed['data'])) {
            foreach ($trashed['data'] as $form) {
                $this->permanentDelete($form['id']);
            }
            Logger::info('Trash emptied', ['count' => count($trashed['data'])]);
        }
    }

    /**
     * Increment the response count for a form.
     */
    public function incrementResponseCount(string $formId): void
    {
        $form = $this->getById($formId);
        if ($form) {
            $count = ($form['response_count'] ?? 0) + 1;
            $this->client->patchDocument('forms', $formId, [
                'response_count' => $count,
            ]);
        }
    }

    /**
     * Get dashboard stats for current user.
     */
    public function getDashboardStats(): array
    {
        $user = Session::get('user');
        $workspaceId = Session::get('current_workspace_id') ?? $user['id'];

        // Get all forms for the workspace
        $allForms = $this->client->listDocuments('forms', [
            'filter[workspace_id]' => $workspaceId,
            'filter[status][ne]'   => 'deleted',
            'limit'               => 100,
        ]);

        $forms = $allForms['data'] ?? [];
        $totalForms = $allForms['meta']['total'] ?? count($forms);
        $published = count(array_filter($forms, fn($f) => ($f['status'] ?? '') === 'published'));
        $totalResponses = array_sum(array_map(fn($f) => $f['response_count'] ?? 0, $forms));

        return [
            'total_forms'     => $totalForms,
            'published_forms' => $published,
            'total_responses' => $totalResponses,
            'recent_forms'    => array_slice($forms, 0, 6),
        ];
    }
}
