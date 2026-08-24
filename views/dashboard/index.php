<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-stats-grid">
    <div class="edf-stat-card">
        <div class="edf-stat-icon purple"><i class="bi bi-file-earmark-text"></i></div>
        <div class="edf-stat-label">Total Forms</div>
        <div class="edf-stat-value"><?= number_format($totalForms) ?></div>
    </div>
    <div class="edf-stat-card">
        <div class="edf-stat-icon green"><i class="bi bi-globe2"></i></div>
        <div class="edf-stat-label">Published</div>
        <div class="edf-stat-value"><?= number_format($publishedForms) ?></div>
    </div>
    <div class="edf-stat-card">
        <div class="edf-stat-icon blue"><i class="bi bi-inboxes"></i></div>
        <div class="edf-stat-label">Total Responses</div>
        <div class="edf-stat-value"><?= number_format($totalResponses) ?></div>
    </div>
    <div class="edf-stat-card">
        <div class="edf-stat-icon orange"><i class="bi bi-bar-chart"></i></div>
        <div class="edf-stat-label">Avg. Conversion</div>
        <div class="edf-stat-value">N/A</div>
    </div>
</div>

<div class="d-flex justify-between align-center mb-3">
    <h2 class="edf-card-title">Recent Forms</h2>
    <a href="/forms" class="edf-btn edf-btn-secondary edf-btn-sm">View All</a>
</div>

<?php if (empty($recentForms)): ?>
<div class="edf-card">
    <div class="edf-empty-state">
        <i class="bi bi-journal-plus"></i>
        <h3>Create your first form</h3>
        <p>Start building beautiful, responsive forms in minutes. Choose from a template or start from scratch.</p>
        <div class="d-flex justify-center gap-2">
            <a href="/forms/create" class="edf-btn edf-btn-primary"><i class="bi bi-plus-lg"></i> Blank Form</a>
            <a href="/templates" class="edf-btn edf-btn-secondary"><i class="bi bi-grid"></i> Browse Templates</a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="edf-forms-grid">
    <?php foreach ($recentForms as $form): ?>
    <div class="edf-form-card" onclick="window.location.href='/forms/<?= $form['id'] ?>/responses'">
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
                        <a href="/f/<?= $form['id'] ?>" target="_blank" class="edf-dropdown-item"><i class="bi bi-box-arrow-up-right"></i> Open Link</a>
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
<?php endif; ?>

<?php \App\Core\View::endSection(); ?>
