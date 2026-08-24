<?php \App\Core\View::extend('layouts.auth'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-auth-wrapper">
    <div class="edf-auth-card">
        <div class="edf-auth-brand">
            <div class="brand-icon">E</div>
            <h1>Reset password</h1>
            <p>We'll send you a reset link</p>
        </div>

        <?php $error = get_flash('error'); if ($error): ?>
        <div class="edf-auth-alert error"><i class="bi bi-exclamation-circle-fill"></i> <?= e($error) ?></div>
        <?php endif; ?>
        <?php $success = get_flash('success'); if ($success): ?>
        <div class="edf-auth-alert success"><i class="bi bi-check-circle-fill"></i> <?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="/forgot-password">
            <?= csrf_field() ?>
            <div class="edf-form-group">
                <label class="edf-label" for="email">Email address</label>
                <input type="email" id="email" name="email" class="edf-input"
                       placeholder="you@example.com" required autofocus>
            </div>
            <button type="submit" class="edf-btn edf-btn-primary">
                <i class="bi bi-envelope"></i> Send Reset Link
            </button>
        </form>

        <div class="edf-auth-links">
            <a href="/login"><i class="bi bi-arrow-left"></i> Back to login</a>
        </div>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
