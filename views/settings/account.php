<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-card" style="max-width: 600px;">
    <div class="edf-card-header">
        <h2 class="edf-card-title">Account Settings</h2>
    </div>
    <div class="edf-card-body">
        <form method="POST" action="/settings/profile">
            <?= csrf_field() ?>
            
            <div class="edf-form-group">
                <label class="edf-label" for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" class="edf-input" value="<?= e($user['display_name'] ?? '') ?>" required>
            </div>
            
            <div class="edf-form-group">
                <label class="edf-label" for="email">Email Address</label>
                <input type="email" id="email" class="edf-input" value="<?= e($user['email'] ?? '') ?>" disabled style="opacity:0.7;background:var(--edf-bg);">
                <div class="edf-form-help">Email address cannot be changed.</div>
            </div>
            
            <div style="margin-top:24px;border-top:1px solid var(--edf-border);padding-top:24px;">
                <h3 style="font-size:14px;font-weight:600;margin:0 0 16px;">Change Password</h3>
                
                <div class="edf-form-group">
                    <label class="edf-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="edf-input">
                </div>
                
                <div class="edf-form-group">
                    <label class="edf-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="edf-input">
                </div>
            </div>
            
            <div style="margin-top:32px;display:flex;justify-content:flex-end;">
                <button type="submit" class="edf-btn edf-btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
