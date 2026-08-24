<?php \App\Core\View::extend('layouts.auth'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-auth-wrapper">
    <div class="edf-auth-card">
        <div class="edf-auth-brand">
            <div class="brand-icon">E</div>
            <h1>Create account</h1>
            <p>Start building beautiful forms</p>
        </div>

        <?php $error = get_flash('error'); if ($error): ?>
        <div class="edf-auth-alert error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/register" id="registerForm">
            <?= csrf_field() ?>

            <div class="edf-form-group">
                <label class="edf-label" for="name">Full name</label>
                <input type="text" id="name" name="name" class="edf-input"
                       placeholder="John Doe" value="<?= e(old('name')) ?>"
                       autocomplete="name" required autofocus>
            </div>

            <div class="edf-form-group">
                <label class="edf-label" for="email">Email address</label>
                <input type="email" id="email" name="email" class="edf-input"
                       placeholder="you@example.com" value="<?= e(old('email')) ?>"
                       autocomplete="email" required>
            </div>

            <div class="edf-form-group">
                <label class="edf-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="edf-input"
                       placeholder="Min 6 characters" autocomplete="new-password" required minlength="6">
            </div>

            <div class="edf-form-group">
                <label class="edf-label" for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="edf-input"
                       placeholder="Re-enter password" autocomplete="new-password" required>
            </div>

            <button type="submit" class="edf-btn edf-btn-primary">
                <i class="bi bi-person-plus"></i> Create Account
            </button>
        </form>

        <div class="edf-auth-links">
            <span style="color:rgba(255,255,255,0.4);">Already have an account?</span>
            <a href="/login">Sign in</a>
        </div>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
