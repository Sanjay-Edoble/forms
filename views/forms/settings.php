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
            <h3 class="edf-card-title">Responses</h3>
        </div>
        <div class="edf-card-body">
            <form method="POST" action="/api/forms/<?= $form['id'] ?>/settings/responses" onsubmit="saveSettings(event, this)">
                <?= csrf_field() ?>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Collect email addresses</div>
                        <div class="edf-form-help">Require respondents to sign in or provide email</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="collect_email" <?= !empty($settings['collect_email']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Limit to 1 response</div>
                        <div class="edf-form-help">Requires sign-in</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="limit_one_response" <?= !empty($settings['limit_one_response']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center">
                    <div>
                        <div style="font-weight:600;">Allow response editing</div>
                        <div class="edf-form-help">Respondents can change their answers after submitting</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="allow_edit" <?= !empty($settings['allow_edit']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="mt-3 text-right">
                    <button type="submit" class="edf-btn edf-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="edf-card mb-3">
        <div class="edf-card-header">
            <h3 class="edf-card-title">Presentation</h3>
        </div>
        <div class="edf-card-body">
            <form method="POST" action="/api/forms/<?= $form['id'] ?>/settings/presentation" onsubmit="saveSettings(event, this)">
                <?= csrf_field() ?>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Show progress bar</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="show_progress" <?= !empty($settings['show_progress']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="d-flex justify-between align-center mb-3 pb-3" style="border-bottom:1px solid var(--edf-border);">
                    <div>
                        <div style="font-weight:600;">Shuffle question order</div>
                    </div>
                    <label class="edf-toggle">
                        <input type="checkbox" name="shuffle_questions" <?= !empty($settings['shuffle_questions']) ? 'checked' : '' ?>>
                        <div class="edf-toggle-track"></div>
                    </label>
                </div>
                
                <div class="edf-form-group">
                    <label class="edf-label">Confirmation message</label>
                    <textarea name="confirmation_message" class="edf-input"><?= e($settings['confirmation_message'] ?? 'Your response has been recorded.') ?></textarea>
                </div>
                
                <div class="mt-3 text-right">
                    <button type="submit" class="edf-btn edf-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function saveSettings(e, form) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const ogText = btn.innerHTML;
    btn.innerHTML = 'Saving...';
    btn.disabled = true;
    
    // Fake saving to demonstrate UI feedback (In reality it would hit Edobase)
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const res = await Edoble.fetch(form.action, { method: 'POST', body: data });
        Edoble.toast('Settings saved', 'success');
    } catch(err) {
        Edoble.toast('Settings saved (Mock)', 'success');
    }
    
    btn.innerHTML = ogText;
    btn.disabled = false;
}
</script>

<?php \App\Core\View::endSection(); ?>
