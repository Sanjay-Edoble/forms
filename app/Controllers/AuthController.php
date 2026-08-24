<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;

/**
 * Authentication controller — login, register, password reset, verification.
 */
class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    /**
     * Show login page.
     */
    public function showLogin(Request $request, array $params): void
    {
        echo view('auth.login', [], 'layouts.auth');
        exit;
    }

    /**
     * Handle login form submission.
     */
    public function login(Request $request, array $params): void
    {
        $email    = trim($request->input('email', ''));
        $password = $request->input('password', '');

        // Validation
        if (empty($email) || empty($password)) {
            flash('error', 'Please enter your email and password.');
            flash('old_input.email', $email);
            redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            flash('old_input.email', $email);
            redirect('/login');
        }

        $result = $this->auth->login($email, $password);

        if ($result['success'] ?? false) {
            $intended = Session::get('intended_url', '/dashboard');
            Session::remove('intended_url');
            flash('success', 'Welcome back!');
            redirect($intended);
        }

        // Login failed
        $message = $result['message'] ?? 'Invalid email or password.';
        flash('error', $message);
        flash('old_input.email', $email);
        redirect('/login');
    }

    /**
     * Show registration page.
     */
    public function showRegister(Request $request, array $params): void
    {
        echo view('auth.register', [], 'layouts.auth');
        exit;
    }

    /**
     * Handle registration form submission.
     */
    public function register(Request $request, array $params): void
    {
        $name     = trim($request->input('name', ''));
        $email    = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $confirm  = $request->input('password_confirmation', '');

        // Validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email)) $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            flash('old_input.name', $name);
            flash('old_input.email', $email);
            redirect('/register');
        }

        $result = $this->auth->register($email, $password, $name);

        if ($result['success'] ?? false) {
            flash('success', 'Account created successfully! Welcome to Edoble Forms.');
            redirect('/dashboard');
        }

        $message = $result['message'] ?? 'Registration failed. Please try again.';
        flash('error', $message);
        flash('old_input.name', $name);
        flash('old_input.email', $email);
        redirect('/register');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request, array $params): void
    {
        $this->auth->logout();
        Session::start(); // Restart for flash message
        flash('success', 'You have been logged out.');
        redirect('/login');
    }

    /**
     * Show forgot password page.
     */
    public function showForgotPassword(Request $request, array $params): void
    {
        echo view('auth.forgot-password', [], 'layouts.auth');
        exit;
    }

    /**
     * Handle forgot password form submission.
     */
    public function forgotPassword(Request $request, array $params): void
    {
        $email = trim($request->input('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('/forgot-password');
        }

        $this->auth->forgotPassword($email);

        // Always show success (don't reveal if email exists)
        flash('success', 'If an account exists with that email, we\'ve sent a password reset link.');
        redirect('/forgot-password');
    }

    /**
     * Show reset password page.
     */
    public function showResetPassword(Request $request, array $params): void
    {
        $token = $request->query('token', '');
        if (empty($token)) {
            flash('error', 'Invalid or missing reset token.');
            redirect('/forgot-password');
        }

        echo view('auth.reset-password', ['token' => $token], 'layouts.auth');
        exit;
    }

    /**
     * Handle reset password form submission.
     */
    public function resetPassword(Request $request, array $params): void
    {
        $token    = $request->input('token', '');
        $password = $request->input('password', '');
        $confirm  = $request->input('password_confirmation', '');

        if (empty($token)) {
            flash('error', 'Invalid reset token.');
            redirect('/forgot-password');
        }

        if (strlen($password) < 6) {
            flash('error', 'Password must be at least 6 characters.');
            redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect('/reset-password?token=' . urlencode($token));
        }

        $result = $this->auth->resetPassword($token, $password);

        if ($result['success'] ?? false) {
            flash('success', 'Password reset successfully. Please log in with your new password.');
            redirect('/login');
        }

        $message = $result['message'] ?? 'Password reset failed. The link may have expired.';
        flash('error', $message);
        redirect('/forgot-password');
    }

    /**
     * Handle email verification.
     */
    public function verifyEmail(Request $request, array $params): void
    {
        $token = $request->query('token', '');

        if (empty($token)) {
            flash('error', 'Invalid verification link.');
            redirect('/dashboard');
        }

        $result = $this->auth->verifyEmail($token);

        if ($result['success'] ?? false) {
            flash('success', 'Email verified successfully!');
        } else {
            flash('error', $result['message'] ?? 'Email verification failed.');
        }

        redirect(is_authenticated() ? '/dashboard' : '/login');
    }
}
