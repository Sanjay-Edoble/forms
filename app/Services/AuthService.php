<?php

namespace App\Services;

use App\Core\Session;
use App\Helpers\Logger;

/**
 * Authentication business logic.
 */
class AuthService
{
    private EdobaseClient $client;

    public function __construct()
    {
        $this->client = new EdobaseClient();
    }

    /**
     * Register a new user.
     */
    public function register(string $email, string $password, string $displayName = ''): array
    {
        $result = $this->client->registerUser($email, $password, $displayName);

        if ($result['success'] ?? false) {
            $user = $result['data'];
            $this->setSession($user);

            Logger::info('User registered', ['email' => $email]);

            // Send verification email
            try {
                $this->client->sendVerificationEmail(
                    $user['token'],
                    url('/verify-email')
                );
            } catch (\Exception $e) {
                Logger::warning('Failed to send verification email', ['error' => $e->getMessage()]);
            }
        }

        return $result;
    }

    /**
     * Log in a user.
     */
    public function login(string $email, string $password): array
    {
        $result = $this->client->loginUser($email, $password);

        if ($result['success'] ?? false) {
            $user = $result['data'];
            $this->setSession($user);
            Logger::info('User logged in', ['email' => $email]);
        }

        return $result;
    }

    /**
     * Log out the current user.
     */
    public function logout(): void
    {
        $token = Session::get('user_token');
        if ($token) {
            try {
                $this->client->logoutUser($token);
            } catch (\Exception $e) {
                // Log but don't block logout
                Logger::warning('Logout API call failed', ['error' => $e->getMessage()]);
            }
        }

        Logger::info('User logged out', ['email' => Session::get('user')['email'] ?? 'unknown']);
        Session::destroy();
    }

    /**
     * Get the current user profile (from session cache or API).
     */
    public function currentUser(): ?array
    {
        return Session::get('user');
    }

    /**
     * Refresh user data from API.
     */
    public function refreshUser(): ?array
    {
        $token = Session::get('user_token');
        if (!$token) {
            return null;
        }

        $result = $this->client->getCurrentUser($token);
        if ($result['success'] ?? false) {
            Session::set('user', $result['data']);
            return $result['data'];
        }

        // Token expired or invalid
        Session::destroy();
        return null;
    }

    /**
     * Request password reset.
     */
    public function forgotPassword(string $email): array
    {
        Logger::info('Password reset requested', ['email' => $email]);
        return $this->client->forgotPassword($email, url('/reset-password'));
    }

    /**
     * Complete password reset.
     */
    public function resetPassword(string $token, string $password): array
    {
        $result = $this->client->resetPassword($token, $password);
        if ($result['success'] ?? false) {
            Logger::info('Password reset completed');
        }
        return $result;
    }

    /**
     * Confirm email verification.
     */
    public function verifyEmail(string $token): array
    {
        return $this->client->confirmVerification($token);
    }

    /**
     * Store user data and token in session.
     */
    private function setSession(array $userData): void
    {
        Session::regenerate();
        Session::set('user_token', $userData['token']);
        Session::set('user', [
            'id'           => $userData['id'],
            'email'        => $userData['email'],
            'display_name' => $userData['display_name'] ?? '',
            'status'       => $userData['status'] ?? 'active',
        ]);
    }
}
