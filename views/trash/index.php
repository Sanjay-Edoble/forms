<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="d-flex justify-between align-center mb-3">
    <p class="text-muted" style="margin:0;">Items in trash will be permanently deleted after 30 days.</p>
    
    <?php if (!empty($forms)): ?>
    <form method="POST" action="/trash/empty" onsubmit="return confirm('Permanently delete all items in trash? This cannot be undone.');" style="margin:0;">
        <?= csrf_field() ?>
        <button type="submit" class="edf-btn edf-btn-danger edf-btn-sm">Empty Trash</button>
    </form>
    <?php endif; ?>
</div>

<?php if (empty($forms)): ?>
<div class="edf-card">
    <div class="edf-empty-state">
        <i class="bi bi-trash3" style="opacity:0.2;"></i>
        <h3>Trash is empty</h3>
        <p>No deleted forms found.</p>
    </div>
</div>
<?php else: ?>
<div class="edf-card">
    <table class="edf-table">
        <thead>
            <tr>
                <th>Form Title</th>
                <th>Deleted On</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($forms as $form): ?>
            <tr>
                <td style="font-weight:500;color:var(--edf-text);"><?= e($form['title']) ?></td>
                <td><?= date('M j, Y', strtotime($form['updated_at'])) ?></td>
                <td style="text-align:right;">
                    <form method="POST" action="/trash/<?= $form['id'] ?>/restore" style="display:inline-block;margin:0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="edf-btn edf-btn-ghost edf-btn-sm" style="color:var(--edf-primary);" title="Restore">
                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </button>
                    </form>
                    <form method="POST" action="/trash/<?= $form['id'] ?>/delete" onsubmit="return confirm('Permanently delete this form?');" style="display:inline-block;margin:0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="edf-btn edf-btn-ghost edf-btn-sm" style="color:var(--edf-danger);" title="Delete Permanently">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php \App\Core\View::endSection(); ?>
