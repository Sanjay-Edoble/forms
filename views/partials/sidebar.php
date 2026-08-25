<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$navItems = [
    ['path' => '/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
    ['path' => '/forms', 'icon' => 'bi-file-earmark-text', 'label' => 'My Forms'],
    ['path' => '/templates', 'icon' => 'bi-grid', 'label' => 'Templates'],
    ['path' => '/trash', 'icon' => 'bi-trash3', 'label' => 'Trash'],
    ['path' => '/settings', 'icon' => 'bi-gear', 'label' => 'Settings'],
];
?>
<aside class="edf-sidebar" id="sidebar">
    <a href="/dashboard" class="edf-sidebar-brand">
        <div class="brand-icon">E</div>
        <span>Edoble <span style="font-weight:400;color:var(--edf-text-muted);">Forms</span></span>
    </a>
    
    <?php if (is_authenticated()): ?>
    <?php
        $workspaces = \App\Core\Session::get('workspaces') ?? [];
        $currentWorkspaceId = \App\Core\Session::get('current_workspace_id');
        if (!empty($workspaces)):
    ?>
    <div style="padding: 0 16px 16px 16px; border-bottom: 1px solid var(--edf-border); margin-bottom: 16px;">
        <form action="/workspace/switch" method="POST" id="workspace-switcher-form">
            <?= csrf_field() ?>
            <select name="workspace_id" class="edf-input" style="width:100%; padding: 8px;" onchange="document.getElementById('workspace-switcher-form').submit()">
                <?php foreach ($workspaces as $w): ?>
                <option value="<?= e($w['id']) ?>" <?= $w['id'] === $currentWorkspaceId ? 'selected' : '' ?>>
                    <?= e($w['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <nav class="edf-sidebar-nav">
        <div class="edf-sidebar-section">
            <div class="edf-sidebar-title">Main</div>
            <?php foreach ($navItems as $item): ?>
            <a href="<?= $item['path'] ?>"
               class="edf-nav-item <?= str_starts_with($currentPath, $item['path']) ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (is_authenticated()): ?>
        <div class="edf-sidebar-section" style="margin-top:auto;padding-top:16px;border-top:1px solid var(--edf-border);">
            <a href="#" data-toggle-theme class="edf-nav-item">
                <i class="bi bi-moon"></i> Toggle Theme
            </a>
        </div>
        <?php endif; ?>
    </nav>
</aside>
