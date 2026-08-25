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

<?php if (empty($questionsData)): ?>
<div class="edf-card">
    <div class="edf-empty-state">
        <i class="bi bi-bar-chart-line" style="opacity:0.2;"></i>
        <h3>No Analytics Yet</h3>
        <p>Once you start receiving responses, charts for your multiple choice, dropdown, and rating questions will appear here.</p>
    </div>
</div>
<?php else: ?>
    <div class="edf-analytics-charts" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 24px;">
        <?php foreach ($questionsData as $index => $qData): ?>
        <div class="edf-card">
            <h3 style="font-size: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--edf-border); padding-bottom: 12px;">
                <?= e($qData['title']) ?>
                <span class="edf-badge edf-badge-neutral" style="float: right;"><?= $qData['total'] ?> responses</span>
            </h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="chart_<?= $index ?>"></canvas>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = <?= json_encode($questionsData) ?>;
            const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9', '#ec4899', '#14b8a6'];
            
            chartData.forEach((q, index) => {
                const ctx = document.getElementById('chart_' + index).getContext('2d');
                const labels = Object.keys(q.distribution);
                const data = Object.values(q.distribution);
                
                // Determine chart type based on question type
                let chartType = 'pie';
                if (['checkboxes', 'rating', 'linear_scale'].includes(q.type) || labels.length > 5) {
                    chartType = 'bar';
                }

                new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels.map(l => l.length > 30 ? l.substring(0, 30) + '...' : l),
                        datasets: [{
                            label: 'Responses',
                            data: data,
                            backgroundColor: chartType === 'pie' ? colors : colors[0],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: chartType === 'pie',
                                position: 'right'
                            }
                        },
                        scales: chartType === 'bar' ? {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        } : {}
                    }
                });
            });
        });
    </script>
<?php endif; ?>

<?php \App\Core\View::endSection(); ?>
