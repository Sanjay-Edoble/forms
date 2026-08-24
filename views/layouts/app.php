<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — Edoble Forms</title>
    <meta name="description" content="Edoble Forms — Create forms, collect responses, analyze data.">
    <link rel="icon" href="<?= asset('images/logo.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset('css/edoble.css') ?>" rel="stylesheet">
    <?= \App\Core\View::yield('head', '') ?>
</head>
<body>
    <div class="edf-app-layout">
        <!-- Sidebar -->
        <?php echo \App\Core\View::partial('partials.sidebar'); ?>

        <!-- Main -->
        <div class="edf-main-content">
            <!-- Topbar -->
            <?php echo \App\Core\View::partial('partials.topbar', ['pageTitle' => $pageTitle ?? 'Dashboard']); ?>

            <!-- Page Content -->
            <div class="edf-page-content">
                <?= \App\Core\View::yield('content', '') ?>
            </div>
        </div>
    </div>

    <!-- Toast container for flash messages -->
    <?php
    $flashSuccess = get_flash('success');
    $flashError = get_flash('error');
    $flashWarning = get_flash('warning');
    ?>

    <script src="<?= asset('js/edoble.js') ?>"></script>
    <?php if ($flashSuccess): ?>
    <script>document.addEventListener('DOMContentLoaded', () => Edoble.toast(<?= json_encode($flashSuccess) ?>, 'success'));</script>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <script>document.addEventListener('DOMContentLoaded', () => Edoble.toast(<?= json_encode($flashError) ?>, 'error'));</script>
    <?php endif; ?>
    <?php if ($flashWarning): ?>
    <script>document.addEventListener('DOMContentLoaded', () => Edoble.toast(<?= json_encode($flashWarning) ?>, 'warning'));</script>
    <?php endif; ?>
    <?= \App\Core\View::yield('scripts', '') ?>
</body>
</html>
