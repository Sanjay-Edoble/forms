<?php \App\Core\View::extend('layouts.auth'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-auth-wrapper">
    <div class="edf-auth-card">
        <div class="edf-auth-brand">
            <div class="brand-icon">E</div>
            <h1>Welcome back</h1>
            <p>Sign in to Edoble Forms</p>
        </div>

        <?php $error = get_flash('error'); if ($error): ?>
        <div class="edf-auth-alert error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <?php $success = get_flash('success'); if ($success): ?>
        <div class="edf-auth-alert success">
            <i class="bi bi-check-circle-fill"></i>
            <?= e($success) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login" id="loginForm">
            <?= csrf_field() ?>

            <div class="edf-form-group">
                <label class="edf-label" for="email">Email address</label>
                <input type="email" id="email" name="email" class="edf-input"
                       placeholder="you@example.com" value="<?= e(old('email')) ?>"
                       autocomplete="email" required autofocus>
            </div>

            <div class="edf-form-group">
                <label class="edf-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="edf-input"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>

            <div class="edf-form-group" style="display:flex;justify-content:space-between;align-items:center;">
                <label class="edf-auth-remember">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="/forgot-password" style="font-size:12.5px;color:rgba(255,255,255,0.5);">Forgot password?</a>
            </div>

            <button type="submit" class="edf-btn edf-btn-primary" id="loginBtn">
                <i class="bi bi-arrow-right-circle"></i> Sign In
            </button>
        </form>

        <div class="edf-auth-links">
            <span style="color:rgba(255,255,255,0.4);">Don't have an account?</span>
            <a href="/register">Create account</a>
        </div>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
