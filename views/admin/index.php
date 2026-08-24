<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-stats-grid">
    <div class="edf-stat-card">
        <div class="edf-stat-icon blue"><i class="bi bi-people"></i></div>
        <div class="edf-stat-label">Total Users</div>
        <div class="edf-stat-value"><?= number_format($stats['users'] ?? 0) ?></div>
    </div>
    <div class="edf-stat-card">
        <div class="edf-stat-icon purple"><i class="bi bi-file-earmark-text"></i></div>
        <div class="edf-stat-label">Total Forms</div>
        <div class="edf-stat-value"><?= number_format($stats['forms'] ?? 0) ?></div>
    </div>
</div>

<div class="edf-card mt-3">
    <div class="edf-card-header">
        <h2 class="edf-card-title">Recent Users</h2>
    </div>
    <div class="edf-card-body" style="padding:0;">
        <table class="edf-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="font-weight:500;"><?= e($u['email']) ?></td>
                    <td><?= e($u['display_name'] ?? 'N/A') ?></td>
                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($users)): ?>
                <tr>
                    <td colspan="3" style="text-align:center;padding:32px;color:var(--edf-text-muted);">No users found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
