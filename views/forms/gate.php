<?php \App\Core\View::extend('layouts.public'); ?>
<?php \App\Core\View::section('head'); ?>
<style>
    .gate-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 40px;
        max-width: 500px;
        margin: 40px auto;
        border-top: 8px solid var(--form-primary);
        text-align: center;
    }
    .gate-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 12px 0;
        color: #0f172a;
    }
    .gate-desc {
        font-size: 15px;
        color: #475569;
        line-height: 1.6;
        margin-bottom: 24px;
    }
    .gate-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-family: inherit;
        font-size: 16px;
        transition: border 0.2s;
        box-sizing: border-box;
        margin-bottom: 16px;
        text-align: center;
    }
    .gate-input:focus {
        outline: none;
        border-color: var(--form-primary);
    }
    .gate-btn {
        background: var(--form-primary);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        width: 100%;
        transition: opacity 0.2s;
    }
    .gate-btn:hover {
        opacity: 0.9;
    }
</style>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<div class="gate-card">
    <h1 class="gate-title"><?= e($form['title']) ?></h1>
    <p class="gate-desc">Please enter your email address to access this form.</p>
    
    <form method="POST" action="/f/<?= $form['id'] ?>/gate">
        <?= csrf_field() ?>
        <input type="email" name="respondent_email" class="gate-input" required placeholder="name@example.com">
        <button type="submit" class="gate-btn">Continue</button>
    </form>
</div>
<?php \App\Core\View::endSection(); ?>
