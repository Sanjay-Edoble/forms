<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-card" style="max-width: 600px; margin: 0 auto;">
    <div class="edf-card-header">
        <h2 class="edf-card-title">Create New Form</h2>
    </div>
    <div class="edf-card-body">
        <form method="POST" action="/forms">
            <?= csrf_field() ?>
            
            <div class="edf-form-group">
                <label class="edf-label" for="title">Form Title</label>
                <input type="text" id="title" name="title" class="edf-input" placeholder="Untitled Form" autofocus required>
            </div>
            
            <div class="edf-form-group" style="margin-top:24px;border-top:1px solid var(--edf-border);padding-top:24px;">
                <label class="edf-label">Or start from a template</label>
                <div style="display:flex;gap:12px;margin-top:12px;">
                    <a href="/templates" class="edf-btn edf-btn-secondary"><i class="bi bi-grid"></i> Browse Templates</a>
                </div>
            </div>
            
            <div style="margin-top:32px;display:flex;justify-content:flex-end;gap:12px;">
                <a href="/forms" class="edf-btn edf-btn-ghost">Cancel</a>
                <button type="submit" class="edf-btn edf-btn-primary">Create Form</button>
            </div>
        </form>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
