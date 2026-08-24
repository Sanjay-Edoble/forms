<?php \App\Core\View::extend('layouts.auth'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-auth-wrapper">
    <div class="edf-auth-card">
        <div class="edf-auth-brand">
            <div class="brand-icon">E</div>
            <h1>New password</h1>
            <p>Create your new password</p>
        </div>

        <?php $error = get_flash('error'); if ($error): ?>
        <div class="edf-auth-alert error"><i class="bi bi-exclamation-circle-fill"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/reset-password">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

            <div class="edf-form-group">
                <label class="edf-label" for="password">New password</label>
                <input type="password" id="password" name="password" class="edf-input"
                       placeholder="Min 6 characters" required minlength="6">
            </div>
            <div class="edf-form-group">
                <label class="edf-label" for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="edf-input"
                       placeholder="Re-enter password" required>
            </div>
            <button type="submit" class="edf-btn edf-btn-primary">
                <i class="bi bi-shield-check"></i> Reset Password
            </button>
        </form>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
