<?php

namespace App\Services;

use App\Helpers\Logger;

/**
 * Central Edobase REST API Client.
 * ALL Edobase API communication goes through this class.
 */
class EdobaseClient
{
    private string $baseUrl;
    private string $publicKey;
    private string $secretKey;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl   = config('edobase.url');
        $this->publicKey = config('edobase.public_key');
        $this->secretKey = config('edobase.secret_key');
        $this->timeout   = config('edobase.timeout', 30);
        $this->retries   = config('edobase.retry', 2);
    }

    // ─── Documents ─────────────────────────────────────────────

    /**
     * Create a document in a collection.
     */
    public function createDocument(string $collection, array $data): array
    {
        return $this->request('POST', "/data/{$collection}", $data);
    }

    /**
     * Get a document by ID.
     */
    public function getDocument(string $collection, string $id, ?string $fields = null): array
    {
        $query = $fields ? "?fields={$fields}" : '';
        return $this->request('GET', "/data/{$collection}/{$id}{$query}");
    }

    /**
     * List documents in a collection.
     */
    public function listDocuments(string $collection, array $params = []): array
    {
        $query = $params ? '?' . http_build_query($params) : '';
        return $this->request('GET', "/data/{$collection}{$query}");
    }

    /**
     * Replace a document entirely (PUT).
     */
    public function updateDocument(string $collection, string $id, array $data): array
    {
        return $this->request('PUT', "/data/{$collection}/{$id}", $data);
    }

    /**
     * Partial update a document (PATCH).
     */
    public function patchDocument(string $collection, string $id, array $data): array
    {
        return $this->request('PATCH', "/data/{$collection}/{$id}", $data);
    }

    /**
     * Delete a document.
     */
    public function deleteDocument(string $collection, string $id): array
    {
        return $this->request('DELETE', "/data/{$collection}/{$id}", [], true);
    }

    /**
     * Search documents with filters, sorting, pagination.
     */
    public function searchDocuments(string $collection, array $params = []): array
    {
        return $this->listDocuments($collection, $params);
    }

    // ─── Collections ───────────────────────────────────────────

    /**
     * List all collections.
     */
    public function listCollections(): array
    {
        return $this->request('GET', '/collections');
    }

    /**
     * Delete a collection.
     */
    public function deleteCollection(string $collection): array
    {
        return $this->request('DELETE', "/collections/{$collection}", [], true);
    }

    // ─── Authentication ────────────────────────────────────────

    /**
     * Register a new end user.
     */
    public function registerUser(string $email, string $password, string $displayName = ''): array
    {
        $data = [
            'email'    => $email,
            'password' => $password,
        ];
        if ($displayName) {
            $data['display_name'] = $displayName;
        }
        return $this->request('POST', '/auth/register', $data);
    }

    /**
     * Log in an end user.
     */
    public function loginUser(string $email, string $password): array
    {
        return $this->request('POST', '/auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    /**
     * Log out an end user.
     */
    public function logoutUser(string $token): array
    {
        return $this->request('POST', '/auth/logout', [], false, $token);
    }

    /**
     * Get the currently authenticated user's profile.
     */
    public function getCurrentUser(string $token): array
    {
        return $this->request('GET', '/auth/me', [], false, $token);
    }

    /**
     * Send verification email.
     */
    public function sendVerificationEmail(string $token, string $verifyUrl): array
    {
        return $this->request('POST', '/auth/verify-email/send', [
            'verify_url' => $verifyUrl,
        ], false, $token);
    }

    /**
     * Confirm email verification.
     */
    public function confirmVerification(string $verificationToken): array
    {
        return $this->request('POST', '/auth/verify-email/confirm', [
            'token' => $verificationToken,
        ]);
    }

    /**
     * Request password reset.
     */
    public function forgotPassword(string $email, string $resetUrl): array
    {
        return $this->request('POST', '/auth/password/forgot', [
            'email'     => $email,
            'reset_url' => $resetUrl,
        ]);
    }

    /**
     * Complete password reset.
     */
    public function resetPassword(string $resetToken, string $newPassword): array
    {
        return $this->request('POST', '/auth/password/reset', [
            'token'    => $resetToken,
            'password' => $newPassword,
        ]);
    }

    // ─── Storage ───────────────────────────────────────────────

    /**
     * Upload a file to Edobase Storage.
     */
    public function uploadFile(string $filePath, string $fileName, ?string $userToken = null): array
    {
        $cfile = new \CURLFile($filePath, mime_content_type($filePath), $fileName);

        $headers = ['X-API-Key: ' . $this->publicKey];
        if ($userToken) {
            $headers[] = 'Authorization: Bearer ' . $userToken;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->baseUrl . '/storage/upload',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['file' => $cfile],
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error('Edobase upload failed', ['error' => $error]);
            return ['success' => false, 'message' => 'Upload failed: ' . $error];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            Logger::error('Edobase upload error', ['status' => $httpCode, 'response' => $decoded]);
        }

        return $decoded ?? ['success' => false, 'message' => 'Invalid response'];
    }

    /**
     * List files.
     */
    public function listFiles(array $params = []): array
    {
        $query = $params ? '?' . http_build_query($params) : '';
        return $this->request('GET', "/storage/files{$query}");
    }

    /**
     * Delete a file.
     */
    public function deleteFile(string $id): array
    {
        return $this->request('DELETE', "/storage/files/{$id}");
    }

    // ─── Mail ──────────────────────────────────────────────────

    /**
     * Send an email via Edobase Mail API.
     * Uses SECRET key (server-side only).
     */
    public function sendMail(string $to, string $subject, string $html): array
    {
        return $this->request('POST', '/mail/send', [
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
        ], true);
    }

    /**
     * Get mail send history.
     */
    public function mailHistory(array $params = []): array
    {
        $query = $params ? '?' . http_build_query($params) : '';
        return $this->request('GET', "/mail/history{$query}", [], true);
    }

    // ─── Health ────────────────────────────────────────────────

    /**
     * Check Edobase API health.
     */
    public function health(): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->baseUrl . '/health',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? ['success' => false];
    }

    // ─── Internal HTTP ─────────────────────────────────────────

    /**
     * Make an API request with retry logic.
     *
     * @param string      $method     HTTP method
     * @param string      $endpoint   API endpoint (e.g., /data/products)
     * @param array       $data       Request body
     * @param bool        $useSecret  Use secret key instead of public key
     * @param string|null $bearerToken End-user bearer token
     */
    private function request(
        string $method,
        string $endpoint,
        array $data = [],
        bool $useSecret = false,
        ?string $bearerToken = null
    ): array {
        $url = $this->baseUrl . $endpoint;
        $apiKey = $useSecret ? $this->secretKey : $this->publicKey;

        $headers = [
            'X-API-Key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if ($bearerToken) {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        $lastError = null;

        for ($attempt = 0; $attempt <= $this->retries; $attempt++) {
            if ($attempt > 0) {
                // Exponential backoff
                usleep((int)(100000 * pow(2, $attempt)));
            }

            $ch = curl_init();
            $opts = [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_SSL_VERIFYPEER => true,
            ];

            switch ($method) {
                case 'POST':
                    $opts[CURLOPT_POST] = true;
                    $opts[CURLOPT_POSTFIELDS] = json_encode($data);
                    break;
                case 'PUT':
                    $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                    $opts[CURLOPT_POSTFIELDS] = json_encode($data);
                    break;
                case 'PATCH':
                    $opts[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                    $opts[CURLOPT_POSTFIELDS] = json_encode($data);
                    break;
                case 'DELETE':
                    $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                    break;
            }

            curl_setopt_array($ch, $opts);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $lastError = $curlError;
                Logger::warning('Edobase request failed (attempt ' . ($attempt + 1) . ')', [
                    'method'   => $method,
                    'endpoint' => $endpoint,
                    'error'    => $curlError,
                ]);
                continue;
            }

            $decoded = json_decode($response, true);

            if ($decoded === null) {
                $lastError = 'Invalid JSON response';
                Logger::error('Edobase invalid response', [
                    'method'   => $method,
                    'endpoint' => $endpoint,
                    'status'   => $httpCode,
                    'body'     => substr($response, 0, 500),
                ]);
                continue;
            }

            // Don't retry client errors (4xx), only server errors (5xx)
            if ($httpCode >= 500) {
                $lastError = $decoded['message'] ?? 'Server error';
                Logger::error('Edobase server error', [
                    'method'   => $method,
                    'endpoint' => $endpoint,
                    'status'   => $httpCode,
                    'message'  => $lastError,
                ]);
                continue;
            }

            // Log 4xx errors but don't retry
            if ($httpCode >= 400) {
                Logger::warning('Edobase client error', [
                    'method'   => $method,
                    'endpoint' => $endpoint,
                    'status'   => $httpCode,
                    'message'  => $decoded['message'] ?? 'Unknown error',
                ]);
            }

            return $decoded;
        }

        // All retries exhausted
        Logger::error('Edobase request failed after all retries', [
            'method'   => $method,
            'endpoint' => $endpoint,
            'error'    => $lastError,
        ]);

        return [
            'success' => false,
            'data'    => null,
            'message' => 'Unable to connect to Edobase. Please try again.',
            'error'   => ['code' => 'CONNECTION_FAILED'],
        ];
    }
}
