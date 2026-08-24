<?php

namespace App\Services;

use App\Helpers\Logger;

/**
 * Response submission, listing, filtering, and management.
 */
class ResponseService
{
    private EdobaseClient $client;

    public function __construct()
    {
        $this->client = new EdobaseClient();
    }

    /**
     * Get the collection name for a form's responses.
     */
    private function collection(string $formId): string
    {
        return "responses_{$formId}";
    }

    /**
     * Submit a response.
     */
    public function submit(string $formId, array $answers, int $formVersion, ?string $email = null, ?string $ipHash = null): array
    {
        $response = [
            'form_id'      => $formId,
            'form_version' => $formVersion,
            'answers'      => json_encode($answers),
            'respondent_email' => $email,
            'ip_hash'      => $ipHash,
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->client->createDocument($this->collection($formId), $response);

        if ($result['success'] ?? false) {
            Logger::info('Response submitted', ['form_id' => $formId]);

            // Increment form response count
            $formService = new FormService();
            $formService->incrementResponseCount($formId);
        }

        return $result;
    }

    /**
     * List responses for a form with pagination, search, filter, sort.
     */
    public function list(string $formId, array $params = []): array
    {
        $defaults = [
            'sort'  => 'created_at',
            'order' => 'desc',
            'limit' => 25,
            'page'  => 1,
        ];

        return $this->client->listDocuments(
            $this->collection($formId),
            array_merge($defaults, $params)
        );
    }

    /**
     * Get responses newer than a timestamp (for live updates).
     */
    public function getNewResponses(string $formId, string $since): array
    {
        return $this->client->listDocuments($this->collection($formId), [
            'filter[created_at][gt]' => $since,
            'sort'                    => 'created_at',
            'order'                   => 'asc',
            'limit'                   => 50,
        ]);
    }

    /**
     * Get a single response by ID.
     */
    public function getById(string $formId, string $responseId): ?array
    {
        $result = $this->client->getDocument($this->collection($formId), $responseId);
        return ($result['success'] ?? false) ? $result['data'] : null;
    }

    /**
     * Update a response.
     */
    public function update(string $formId, string $responseId, array $answers): array
    {
        return $this->client->patchDocument($this->collection($formId), $responseId, [
            'answers'    => json_encode($answers),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete a response.
     */
    public function delete(string $formId, string $responseId): array
    {
        Logger::info('Response deleted', ['form_id' => $formId, 'response_id' => $responseId]);
        return $this->client->deleteDocument($this->collection($formId), $responseId);
    }

    /**
     * Bulk delete responses.
     */
    public function bulkDelete(string $formId, array $responseIds): array
    {
        $results = [];
        foreach ($responseIds as $id) {
            $results[] = $this->delete($formId, $id);
        }
        return ['success' => true, 'deleted' => count($responseIds)];
    }

    /**
     * Get all responses for export (paginated internally).
     */
    public function getAllForExport(string $formId, array $filters = []): array
    {
        $allResponses = [];
        $page = 1;
        $perPage = 100;

        do {
            $params = array_merge($filters, [
                'page'  => $page,
                'limit' => $perPage,
                'sort'  => 'created_at',
                'order' => 'asc',
            ]);

            $result = $this->client->listDocuments($this->collection($formId), $params);

            if (!($result['success'] ?? false)) {
                break;
            }

            $allResponses = array_merge($allResponses, $result['data'] ?? []);
            $hasMore = $result['meta']['has_more'] ?? false;
            $page++;

        } while ($hasMore && $page <= 100); // Safety limit

        return $allResponses;
    }

    /**
     * Get response statistics for analytics.
     */
    public function getStats(string $formId): array
    {
        // Get total count
        $total = $this->client->listDocuments($this->collection($formId), ['limit' => 1]);
        $totalCount = $total['meta']['total'] ?? 0;

        // Get today's responses
        $today = date('Y-m-d');
        $todayResponses = $this->client->listDocuments($this->collection($formId), [
            'filter[submitted_at][gte]' => $today . ' 00:00:00',
            'limit' => 1,
        ]);
        $todayCount = $todayResponses['meta']['total'] ?? 0;

        // Get this week
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekResponses = $this->client->listDocuments($this->collection($formId), [
            'filter[submitted_at][gte]' => $weekStart . ' 00:00:00',
            'limit' => 1,
        ]);
        $weekCount = $weekResponses['meta']['total'] ?? 0;

        // Get this month
        $monthStart = date('Y-m-01');
        $monthResponses = $this->client->listDocuments($this->collection($formId), [
            'filter[submitted_at][gte]' => $monthStart . ' 00:00:00',
            'limit' => 1,
        ]);
        $monthCount = $monthResponses['meta']['total'] ?? 0;

        return [
            'total'      => $totalCount,
            'today'      => $todayCount,
            'this_week'  => $weekCount,
            'this_month' => $monthCount,
        ];
    }
}
