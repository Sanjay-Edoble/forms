<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="d-flex justify-between align-center mb-3">
    <div class="edf-topbar-search" style="min-width:300px;margin:0;">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search templates...">
    </div>
    
    <div class="d-flex gap-2">
        <select class="edf-input" style="width:auto;padding:7px 32px 7px 14px;">
            <option value="">All Categories</option>
            <option value="business">Business</option>
            <option value="education">Education</option>
            <option value="personal">Personal</option>
        </select>
    </div>
</div>

<div class="edf-forms-grid">
    <?php 
    foreach ($templates as $tpl):
    ?>
    <div class="edf-form-card">
        <div class="edf-form-card-header" style="background: <?= json_decode($tpl['theme'], true)['primary_color'] ?? '#6366f1' ?>;"></div>
        <div class="edf-form-card-body">
            <h3 class="edf-form-card-title"><?= e($tpl['title']) ?></h3>
            <div class="edf-form-card-desc" style="min-height:40px;"><?= e($tpl['description']) ?></div>
            
            <div class="d-flex justify-between align-center mt-3">
                <span class="edf-badge edf-badge-neutral"><?= e($tpl['category']) ?></span>
                <form method="POST" action="/templates/<?= e($tpl['id']) ?>/use" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="edf-btn edf-btn-primary edf-btn-sm">Use Template</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php \App\Core\View::endSection(); ?>
