<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-builder-toolbar">
    <div class="edf-builder-tabs">
        <a href="/forms/<?= $form['id'] ?>/edit">Questions</a>
        <a href="/forms/<?= $form['id'] ?>/settings" class="active">Settings</a>
        <a href="/forms/<?= $form['id'] ?>/responses">Responses</a>
    </div>
    <div class="edf-builder-actions">
        <a href="/forms/<?= $form['id'] ?>/preview" target="_blank" class="edf-btn edf-btn-ghost edf-btn-icon"><i class="bi bi-eye"></i></a>
    </div>
</div>

<div style="padding-top:80px; max-width:800px; margin:0 auto;">
    
    <div class="edf-card mb-3">
        <div class="edf-card-header">
            <h3 class="edf-card-title">Access Control & Limits</h3>
        </div>
        <div class="edf-card-body">
            <form method="POST" action="/forms/<?= $form['id'] ?>/settings">
                <?= csrf_field() ?>
                
                <?php $error = get_flash('error'); if ($error): ?><div class="edf-alert error" style="margin-bottom:16px;color:#ef4444;"><?= e($error) ?></div><?php endif; ?>
                <?php $success = get_flash('success'); if ($success): ?><div class="edf-alert success" style="margin-bottom:16px;color:#10b981;"><?= e($success) ?></div><?php endif; ?>

                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Require Email to Start</div>
                        <div class="edf-form-help">Respondents must provide an email before seeing the form questions.</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="require_email" value="1" <?= !empty($settings['require_email']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Limit to 1 response per email</div>
                        <div class="edf-form-help">Requires "Require Email to Start" to be enabled.</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="limit_one_response" value="1" <?= !empty($settings['limit_one_response']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Verify email via Magic Link</div>
                        <div class="edf-form-help">Sends a unique link to the respondent's email to verify ownership. Requires "Require Email to Start" to be enabled.</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="verify_email_magic_link" value="1" <?= !empty($settings['verify_email_magic_link']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <h3 class="edf-card-title mt-4 mb-3">Presentation</h3>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Presentation Mode (One question per page)</div>
                        <div class="edf-form-help">Displays questions one at a time with smooth sliding animations, instead of a scrolling list.</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="presentation_mode" value="1" <?= !empty($settings['presentation_mode']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Show progress bar</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="show_progress" value="1" <?= !empty($settings['show_progress']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Shuffle question order</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="shuffle_questions" value="1" <?= !empty($settings['shuffle_questions']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="edf-form-group">
                    <label class="edf-label">Confirmation message</label>
                    <textarea name="confirmation_message" class="edf-input"><?= e($settings['confirmation_message'] ?? 'Your response has been recorded.') ?></textarea>
                </div>
                
                <h3 class="edf-card-title mt-4 mb-3">Integrations</h3>

                <div class="edf-form-group mb-3 pb-3">
                    <label class="edf-label">Webhook URL</label>
                    <input type="url" name="webhook_url" class="edf-input" value="<?= e($settings['webhook_url'] ?? '') ?>" placeholder="https://api.example.com/webhook">
                    <div class="edf-form-help">Sends a JSON POST request with form data whenever a respondent submits the form.</div>
                </div>
                
                <div class="mt-3 text-right">
                    <button type="submit" class="edf-btn edf-btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

<?php \App\Core\View::endSection(); ?>
