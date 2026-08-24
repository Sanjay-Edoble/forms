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
    $mockTemplates = [
        ['id' => 't1', 'title' => 'Contact Information', 'desc' => 'Basic contact form for general inquiries.', 'category' => 'Business', 'color' => '#6366f1'],
        ['id' => 't2', 'title' => 'Event Registration', 'desc' => 'Collect RSVPs and details for your upcoming event.', 'category' => 'Personal', 'color' => '#10b981'],
        ['id' => 't3', 'title' => 'Customer Feedback', 'desc' => 'Gather feedback on your product or service.', 'category' => 'Business', 'color' => '#f59e0b'],
        ['id' => 't4', 'title' => 'Job Application', 'desc' => 'Standard application form for hiring candidates.', 'category' => 'Business', 'color' => '#ec4899'],
        ['id' => 't5', 'title' => 'Course Evaluation', 'desc' => 'End-of-semester survey for student feedback.', 'category' => 'Education', 'color' => '#3b82f6'],
        ['id' => 't6', 'title' => 'Party Invite', 'desc' => 'Fun and casual party invitation form.', 'category' => 'Personal', 'color' => '#8b5cf6'],
    ];
    
    foreach ($mockTemplates as $tpl):
    ?>
    <div class="edf-form-card">
        <div class="edf-form-card-header" style="background: <?= $tpl['color'] ?>;"></div>
        <div class="edf-form-card-body">
            <h3 class="edf-form-card-title"><?= e($tpl['title']) ?></h3>
            <div class="edf-form-card-desc" style="min-height:40px;"><?= e($tpl['desc']) ?></div>
            
            <div class="d-flex justify-between align-center mt-3">
                <span class="edf-badge edf-badge-neutral"><?= e($tpl['category']) ?></span>
                <form method="POST" action="/forms" style="margin:0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="title" value="<?= e($tpl['title']) ?>">
                    <!-- In a real app, you'd pass a template_id to clone its schema -->
                    <button type="submit" class="edf-btn edf-btn-primary edf-btn-sm">Use Template</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php \App\Core\View::endSection(); ?>
