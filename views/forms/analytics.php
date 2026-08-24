<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="d-flex justify-between align-center mb-3">
    <div class="d-flex align-center gap-3">
        <a href="/forms/<?= $form['id'] ?>/responses" class="edf-btn edf-btn-ghost edf-btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h2 class="edf-page-title">Analytics: <?= e($form['title']) ?></h2>
    </div>
</div>

<div class="edf-stats-grid">
    <div class="edf-stat-card">
        <div class="edf-stat-icon blue"><i class="bi bi-inboxes"></i></div>
        <div class="edf-stat-label">Total Responses</div>
        <div class="edf-stat-value"><?= number_format($form['response_count'] ?? 0) ?></div>
    </div>
    <div class="edf-stat-card">
        <div class="edf-stat-icon green"><i class="bi bi-graph-up"></i></div>
        <div class="edf-stat-label">Completion Rate</div>
        <div class="edf-stat-value">94%</div>
    </div>
    <div class="edf-stat-card">
        <div class="edf-stat-icon purple"><i class="bi bi-clock-history"></i></div>
        <div class="edf-stat-label">Avg. Time</div>
        <div class="edf-stat-value">1m 24s</div>
    </div>
</div>

<div class="edf-card">
    <div class="edf-empty-state">
        <i class="bi bi-bar-chart-line"></i>
        <h3>Analytics Dashboard</h3>
        <p>In a fully productionized version, this section would render Chart.js graphs mapping the aggregate breakdown of responses per question.</p>
        <p>Currently showing mock aggregate data for demonstration.</p>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
