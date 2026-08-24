<?php \App\Core\View::extend('layouts.public'); ?>
<?php \App\Core\View::section('head'); ?>
<style>
    :root {
        --form-primary: <?= e($theme['primary_color'] ?? '#6366f1') ?>;
        --form-bg: <?= e($theme['bg_color'] ?? '#f0ebf8') ?>;
        --form-font: "<?= e($theme['font'] ?? 'Inter') ?>", sans-serif;
    }
    body {
        background-color: var(--form-bg);
        font-family: var(--form-font);
        padding: 40px 20px;
        min-height: 100vh;
    }
    .public-form-wrapper {
        max-width: 640px;
        margin: 0 auto;
    }
    .public-form-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        margin-bottom: 16px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    .public-form-header {
        border-top: 10px solid var(--form-primary);
        padding: 32px;
    }
    .public-form-title {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 12px;
        color: #111827;
        line-height: 1.2;
    }
    .public-form-desc {
        font-size: 14.5px;
        color: #4b5563;
        line-height: 1.6;
        white-space: pre-wrap;
    }
    .public-form-required-note {
        color: #ef4444;
        font-size: 13px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    
    .question-card {
        padding: 24px 32px;
    }
    .question-card.error {
        border: 1px solid #ef4444;
    }
    .question-title {
        font-size: 16px;
        font-weight: 500;
        margin: 0 0 16px;
        color: #111827;
    }
    .req-star { color: #ef4444; margin-left: 4px; }
    
    .q-input {
        width: 100%;
        border: none;
        border-bottom: 1px solid #d1d5db;
        padding: 8px 0;
        font-size: 15px;
        font-family: var(--form-font);
        transition: border-color 0.2s;
        background: transparent;
    }
    .q-input:focus {
        outline: none;
        border-bottom-color: var(--form-primary);
        border-bottom-width: 2px;
        padding-bottom: 7px;
    }
    textarea.q-input { resize: vertical; min-height: 60px; }
    
    .q-choice-label {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 15px;
        color: #374151;
    }
    .q-choice-label input {
        width: 18px;
        height: 18px;
        accent-color: var(--form-primary);
        cursor: pointer;
    }
    
    .scale-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .scale-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .scale-label { font-size: 13px; color: #4b5563; }
    
    .submit-btn {
        background: var(--form-primary);
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
        font-family: var(--form-font);
    }
    .submit-btn:hover { opacity: 0.9; }
    
    .footer-branding {
        text-align: center;
        margin-top: 32px;
        font-size: 12px;
        color: #6b7280;
    }
    .footer-branding a { color: #6b7280; text-decoration: underline; }
    
    .error-msg {
        color: #ef4444;
        font-size: 12px;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
</style>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<div class="public-form-wrapper">
    
    <?php $error = get_flash('error'); if ($error): ?>
    <div style="background:#fee2e2;color:#991b1b;padding:16px;border-radius:8px;margin-bottom:16px;font-size:14px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-exclamation-circle-fill"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="/f/<?= e($form['id']) ?>">
        <?= csrf_field() ?>
        
        <div class="public-form-card public-form-header">
            <h1 class="public-form-title"><?= e($form['title']) ?></h1>
            <?php if (!empty($form['description'])): ?>
                <div class="public-form-desc"><?= nl2br(e($form['description'])) ?></div>
            <?php endif; ?>
            <div class="public-form-required-note">
                * Indicates required question
            </div>
        </div>
        
        <?php foreach ($schema['questions'] ?? [] as $q): 
            $id = e($q['id']);
            $title = e($q['title']);
            $type = $q['type'];
            $req = !empty($q['required']);
            $hasError = isset($errors[$id]);
        ?>
        <div class="public-form-card question-card <?= $hasError ? 'error' : '' ?>">
            <div class="question-title">
                <?= $title ?><?= $req ? '<span class="req-star">*</span>' : '' ?>
            </div>
            
            <?php if ($type === 'short_text' || $type === 'email' || $type === 'phone'): ?>
                <input type="<?= $type === 'email' ? 'email' : ($type === 'phone' ? 'tel' : 'text') ?>" 
                       name="answers[<?= $id ?>]" 
                       class="q-input" 
                       placeholder="Your answer"
                       <?= $req ? 'required' : '' ?>
                       value="<?= e(old("answers.{$id}")) ?>">
                       
            <?php elseif ($type === 'paragraph'): ?>
                <textarea name="answers[<?= $id ?>]" 
                          class="q-input" 
                          placeholder="Your answer"
                          <?= $req ? 'required' : '' ?>><?= e(old("answers.{$id}")) ?></textarea>
                          
            <?php elseif ($type === 'multiple_choice'): ?>
                <?php foreach ($q['options'] ?? [] as $opt): $v = e($opt['value']); ?>
                <label class="q-choice-label">
                    <input type="radio" name="answers[<?= $id ?>]" value="<?= $v ?>" <?= $req ? 'required' : '' ?> <?= old("answers.{$id}") === $v ? 'checked' : '' ?>>
                    <?= $v ?>
                </label>
                <?php endforeach; ?>
                
            <?php elseif ($type === 'checkboxes'): ?>
                <?php $oldArr = old("answers.{$id}", []); if (!is_array($oldArr)) $oldArr = []; ?>
                <?php foreach ($q['options'] ?? [] as $opt): $v = e($opt['value']); ?>
                <label class="q-choice-label">
                    <input type="checkbox" name="answers[<?= $id ?>][]" value="<?= $v ?>" <?= in_array($v, $oldArr) ? 'checked' : '' ?>>
                    <?= $v ?>
                </label>
                <?php endforeach; ?>
                
            <?php elseif ($type === 'dropdown'): ?>
                <select name="answers[<?= $id ?>]" class="q-input" <?= $req ? 'required' : '' ?> style="border:1px solid #d1d5db;border-radius:4px;padding:12px;">
                    <option value="">Choose</option>
                    <?php foreach ($q['options'] ?? [] as $opt): $v = e($opt['value']); ?>
                        <option value="<?= $v ?>" <?= old("answers.{$id}") === $v ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
                
            <?php elseif ($type === 'linear_scale'): 
                $min = (int)($q['scaleMin'] ?? 1);
                $max = (int)($q['scaleMax'] ?? 5);
            ?>
                <div class="scale-container">
                    <?php if (!empty($q['minLabel'])): ?>
                        <div class="scale-label" style="margin-right:12px;"><?= e($q['minLabel']) ?></div>
                    <?php endif; ?>
                    
                    <?php for ($i = $min; $i <= $max; $i++): ?>
                    <div class="scale-item">
                        <span><?= $i ?></span>
                        <input type="radio" name="answers[<?= $id ?>]" value="<?= $i ?>" <?= $req ? 'required' : '' ?> style="width:18px;height:18px;accent-color:var(--form-primary);" <?= old("answers.{$id}") == $i ? 'checked' : '' ?>>
                    </div>
                    <?php endfor; ?>
                    
                    <?php if (!empty($q['maxLabel'])): ?>
                        <div class="scale-label" style="margin-left:12px;"><?= e($q['maxLabel']) ?></div>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($type === 'date'): ?>
                <input type="date" name="answers[<?= $id ?>]" class="q-input" style="max-width:200px;" <?= $req ? 'required' : '' ?> value="<?= e(old("answers.{$id}")) ?>">
                
            <?php elseif ($type === 'time'): ?>
                <input type="time" name="answers[<?= $id ?>]" class="q-input" style="max-width:150px;" <?= $req ? 'required' : '' ?> value="<?= e(old("answers.{$id}")) ?>">
                
            <?php endif; ?>
            
            <?php if ($hasError): ?>
                <div class="error-msg"><i class="bi bi-exclamation-triangle"></i> <?= e($errors[$id]) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;">
            <button type="submit" class="submit-btn">Submit</button>
            <div style="font-size:12px;color:#6b7280;">Never submit passwords through forms.</div>
        </div>
    </form>
    
    <div class="footer-branding">
        Powered by <a href="/" target="_blank"><b>Edoble Forms</b></a>
    </div>
</div>
<?php \App\Core\View::endSection(); ?>
