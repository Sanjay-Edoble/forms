<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div class="d-flex justify-between align-center mb-3">
    <div class="d-flex align-center gap-3">
        <a href="/forms/<?= $form['id'] ?>/responses" class="edf-btn edf-btn-ghost edf-btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h2 class="edf-page-title">Response Detail</h2>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="/forms/<?= $form['id'] ?>/responses/<?= $response['id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this response?');" style="margin:0;">
            <?= csrf_field() ?>
            <button type="submit" class="edf-btn edf-btn-danger"><i class="bi bi-trash"></i> Delete Response</button>
        </form>
    </div>
</div>

<div class="edf-card" style="max-width: 800px;">
    <div class="edf-card-header">
        <div>
            <div style="font-weight:700;font-size:15px;">Submitted: <?= date('M j, Y h:i A', strtotime($response['submitted_at'] ?? $response['created_at'])) ?></div>
            <?php if (!empty($response['respondent_email'])): ?>
                <div class="text-muted" style="font-size:13px;margin-top:2px;">Respondent: <?= e($response['respondent_email']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="edf-card-body">
        <table class="edf-table" style="border:1px solid var(--edf-border); border-radius:var(--edf-radius-sm); overflow:hidden;">
            <thead>
                <tr>
                    <th style="width:40%;">Question</th>
                    <th style="width:60%;">Answer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schema['questions'] ?? [] as $q): 
                    if (($q['type'] ?? '') === 'section') continue;
                    $val = $answers[$q['id']] ?? null;
                ?>
                <tr>
                    <td style="font-weight:500;color:var(--edf-text);"><?= e($q['title'] ?? 'Untitled') ?></td>
                    <td>
                        <?php if ($val === null || $val === ''): ?>
                            <span class="text-muted"><i>No answer provided</i></span>
                        <?php elseif (is_array($val)): ?>
                            <ul style="margin:0;padding-left:16px;">
                                <?php foreach ($val as $v): ?>
                                    <li><?= e($v) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <?= nl2br(e((string)$val)) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
