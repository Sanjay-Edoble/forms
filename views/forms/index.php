<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="d-flex justify-between align-center mb-3">
    <form method="GET" action="/forms" class="edf-topbar-search" style="min-width:300px;margin:0;">
        <i class="bi bi-search"></i>
        <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Search forms...">
        <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    </form>

    <div class="d-flex gap-2">
        <select class="edf-input" style="width:auto;padding:7px 32px 7px 14px;" onchange="window.location.href='?search=<?= urlencode($search) ?>&status='+this.value">
            <option value="">All Statuses</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
    </div>
</div>

<?php if (empty($forms)): ?>
<div class="edf-card">
    <div class="edf-empty-state">
        <i class="bi bi-search"></i>
        <h3>No forms found</h3>
        <p>We couldn't find any forms matching your search criteria.</p>
        <?php if ($search || $status): ?>
            <a href="/forms" class="edf-btn edf-btn-secondary">Clear Filters</a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="edf-forms-grid">
    <?php foreach ($forms as $form): ?>
    <div class="edf-form-card" onclick="window.location.href='/forms/<?= $form['id'] ?>/edit'">
        <div class="edf-form-card-header"></div>
        <div class="edf-form-card-body">
            <div class="d-flex justify-between align-start mb-2">
                <div>
                    <h3 class="edf-form-card-title"><?= e($form['title']) ?></h3>
                    <div class="edf-badge <?= ($form['status'] === 'published') ? 'edf-badge-live' : 'edf-badge-neutral' ?>">
                        <?= ucfirst($form['status'] ?? 'draft') ?>
                    </div>
                </div>
                <div class="edf-dropdown" onclick="event.stopPropagation()">
                    <button class="edf-btn-ghost edf-btn-icon" data-dropdown><i class="bi bi-three-dots-vertical"></i></button>
                    <div class="edf-dropdown-menu">
                        <a href="/forms/<?= $form['id'] ?>/edit" class="edf-dropdown-item"><i class="bi bi-pencil"></i> Edit Form</a>
                        <a href="/forms/<?= $form['id'] ?>/responses" class="edf-dropdown-item"><i class="bi bi-inboxes"></i> Responses</a>
                        <a href="/forms/<?= $form['id'] ?>/share" class="edf-dropdown-item"><i class="bi bi-share"></i> Share</a>
                        <div class="edf-dropdown-divider"></div>
                        <form method="POST" action="/forms/<?= $form['id'] ?>/duplicate" style="margin:0;">
                            <?= csrf_field() ?>
                            <button type="submit" class="edf-dropdown-item"><i class="bi bi-copy"></i> Duplicate</button>
                        </form>
                        <form method="POST" action="/forms/<?= $form['id'] ?>/delete" style="margin:0;" onsubmit="return confirm('Move to trash?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="edf-dropdown-item danger"><i class="bi bi-trash"></i> Move to Trash</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="edf-form-card-desc">
                <?= e(mb_strimwidth($form['description'] ?? 'No description', 0, 60, '...')) ?>
            </div>
            
            <div class="edf-form-card-meta">
                <span><i class="bi bi-clock"></i> <?= time_ago($form['updated_at']) ?></span>
                <span style="color:var(--edf-primary);font-weight:600;"><i class="bi bi-reply"></i> <?= number_format($form['response_count'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php 
$totalPages = ceil(($meta['total'] ?? 0) / ($meta['limit'] ?? 12));
$currentPage = $meta['page'] ?? 1;
if ($totalPages > 1): 
?>
<div class="edf-pagination">
    <?php if ($currentPage > 1): ?>
        <a href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><i class="bi bi-chevron-left"></i></a>
    <?php endif; ?>
    
    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="<?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    
    <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><i class="bi bi-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
<?php \App\Core\View::endSection(); ?>
