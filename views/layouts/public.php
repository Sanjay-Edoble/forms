<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Form') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --form-bg: <?= $theme['bg_color'] ?? '#f8f9fc' ?>;
            --form-primary: <?= $theme['primary_color'] ?? '#6366f1' ?>;
            --form-font: '<?= $theme['font'] ?? 'Inter' ?>', sans-serif;
        }
        body {
            margin: 0; padding: 0;
            background-color: var(--form-bg);
            font-family: var(--form-font);
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .public-wrapper {
            max-width: 768px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .edf-alert {
            padding: 12px 16px; margin-bottom: 20px; border-radius: 8px; font-size: 14px;
        }
        .edf-alert.error { background: #fee2e2; color: #b91c1c; }
        .edf-alert.success { background: #dcfce3; color: #15803d; }
    </style>
    <?= \App\Core\View::yield('head', '') ?>
</head>
<body>
    <div class="public-wrapper">
        <?php $error = get_flash('error'); if ($error): ?>
            <div class="edf-alert error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php $success = get_flash('success'); if ($success): ?>
            <div class="edf-alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <?= \App\Core\View::yield('content', '') ?>
    </div>
    
    <div style="text-align:center; padding: 20px; color: #94a3b8; font-size: 13px; margin-top: 40px;">
        Powered by <strong>Edoble Forms</strong>
    </div>

    <?= \App\Core\View::yield('scripts', '') ?>
</body>
</html>
