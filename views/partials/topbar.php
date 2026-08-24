<?php $user = current_user(); ?>
<header class="edf-topbar">
    <div class="edf-topbar-left">
        <button class="edf-btn edf-btn-ghost edf-mobile-toggle edf-btn-icon" data-toggle-sidebar>
            <i class="bi bi-list"></i>
        </button>
        <h1 class="edf-page-title"><?= e($pageTitle ?? 'Dashboard') ?></h1>
    </div>
    <div class="edf-topbar-right">
        <a href="/forms/create" class="edf-btn edf-btn-primary edf-btn-sm">
            <i class="bi bi-plus-lg"></i> Create Form
        </a>

        <div class="edf-dropdown">
            <button class="edf-btn edf-btn-ghost edf-btn-icon" data-dropdown>
                <i class="bi bi-person-circle" style="font-size:20px;"></i>
            </button>
            <div class="edf-dropdown-menu">
                <div style="padding:10px 14px;border-bottom:1px solid var(--edf-border);">
                    <div style="font-weight:600;font-size:13px;"><?= e($user['display_name'] ?? 'User') ?></div>
                    <div style="font-size:11.5px;color:var(--edf-text-muted);"><?= e($user['email'] ?? '') ?></div>
                </div>
                <a href="/settings" class="edf-dropdown-item"><i class="bi bi-gear"></i> Settings</a>
                <a href="#" data-toggle-theme class="edf-dropdown-item"><i class="bi bi-moon"></i> Theme</a>
                <?php if (in_array($user['email'] ?? '', config('app.admin_emails', []))): ?>
                <a href="/admin" class="edf-dropdown-item"><i class="bi bi-shield-check"></i> Admin</a>
                <?php endif; ?>
                <div class="edf-dropdown-divider"></div>
                <form method="POST" action="/logout" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="edf-dropdown-item danger"><i class="bi bi-box-arrow-right"></i> Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>
