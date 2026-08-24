<?php \App\Core\View::extend('layouts.public'); ?>
<?php \App\Core\View::section('head'); ?>
<style>
    body { background-color: #f0ebf8; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
    .success-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 40px; text-align: center; max-width: 500px; width: 100%; border-top: 10px solid #6366f1; }
    .success-icon { font-size: 64px; color: #10b981; margin-bottom: 16px; }
    .success-title { font-size: 24px; font-weight: 700; color: #111827; margin: 0 0 12px; }
    .success-text { font-size: 15px; color: #4b5563; margin: 0 0 32px; }
    .submit-another { display: inline-block; padding: 10px 20px; color: #6366f1; font-weight: 600; text-decoration: none; border: 1px solid #6366f1; border-radius: 6px; transition: all 0.2s; }
    .submit-another:hover { background: #6366f1; color: #fff; }
</style>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<div class="success-card">
    <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
    <h1 class="success-title"><?= e($form['title'] ?? 'Form Submitted') ?></h1>
    <p class="success-text">Your response has been recorded successfully.</p>
    
    <a href="/f/<?= e($form['id']) ?>" class="submit-another">Submit another response</a>
</div>
<?php \App\Core\View::endSection(); ?>
