<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="edf-card" style="max-width: 700px; margin: 0 auto;">
    <div class="edf-card-header">
        <h2 class="edf-card-title">Share Form: <?= e($form['title']) ?></h2>
    </div>
    <div class="edf-card-body">
        
        <?php if ($form['status'] !== 'published'): ?>
        <div style="background:var(--edf-warning-light);color:var(--edf-warning);padding:16px;border-radius:8px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:24px;"></i>
            <div>
                <strong>This form is not published.</strong><br>
                Only you can view it. Publish the form in the builder to allow others to submit responses.
            </div>
        </div>
        <?php endif; ?>
        
        <div class="edf-form-group">
            <label class="edf-label">Share Link</label>
            <div style="display:flex;gap:8px;">
                <input type="text" id="shareLink" class="edf-input" value="<?= config('app.url') ?>/f/<?= $form['id'] ?>" readonly>
                <button type="button" class="edf-btn edf-btn-primary" onclick="copyLink()">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
            <div class="edf-form-help">Anyone with this link can respond to the form.</div>
        </div>
        
        <div class="edf-form-group mt-3 pt-3" style="border-top:1px solid var(--edf-border);">
            <label class="edf-label">Embed HTML</label>
            <textarea class="edf-input" id="embedCode" readonly style="font-family:monospace;font-size:12px;min-height:80px;"><iframe src="<?= config('app.url') ?>/f/<?= $form['id'] ?>?embed=true" width="100%" height="600" frameborder="0" marginheight="0" marginwidth="0">Loading…</iframe></textarea>
            <button type="button" class="edf-btn edf-btn-secondary mt-2" onclick="copyEmbed()">
                <i class="bi bi-code-slash"></i> Copy Embed Code
            </button>
        </div>
        
    </div>
</div>

<script>
function copyLink() {
    const input = document.getElementById('shareLink');
    input.select();
    document.execCommand('copy');
    Edoble.toast('Link copied to clipboard!', 'success');
}
function copyEmbed() {
    const input = document.getElementById('embedCode');
    input.select();
    document.execCommand('copy');
    Edoble.toast('Embed code copied to clipboard!', 'success');
}
</script>

<?php \App\Core\View::endSection(); ?>
