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
    <div class="gate-container">
        <?php if (get_flash('magic_link_sent')): ?>
            <div class="gate-card" style="text-align: center;">
                <h1 class="gate-title" style="margin-bottom: 8px;">Check Your Email</h1>
                <div class="gate-desc" style="font-size: 15px;">
                    A secure magic link has been sent to your email address. Please click the link in that email to access the form.
                </div>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b;">
                    If you did not receive it, please contact the form owner.
                </div>
            </div>
        <?php else: ?>
            <div class="gate-card">
                <h1 class="gate-title"><?= e($form['title']) ?></h1>
                <div class="gate-desc">
                    This form requires an email address to participate. Please enter your email below to continue.
                </div>

                <form method="POST" action="/f/<?= $form['id'] ?>/gate">
                    <?= csrf_field() ?>
                    <div style="margin-bottom: 24px;">
                        <input type="email" name="respondent_email" class="gate-input" placeholder="you@example.com" required autofocus>
                    </div>
                    <button type="submit" class="gate-btn">Continue to Form</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php \App\Core\View::endSection(); ?>
