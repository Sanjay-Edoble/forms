<?php \App\Core\View::extend('layouts.public'); ?>
<?php \App\Core\View::section('content'); ?>
<div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 40px; text-align: center; border-top: 8px solid var(--form-primary);">
    <i class="bi bi-door-closed" style="font-size: 48px; color: #94a3b8; margin-bottom: 16px; display: block;"></i>
    <h2 style="margin: 0 0 12px 0; color: #0f172a;">Form Closed</h2>
    <p style="color: #475569; font-size: 16px; margin: 0;"><?= e($message ?? 'This form is no longer accepting responses.') ?></p>
</div>
<?php \App\Core\View::endSection(); ?>
