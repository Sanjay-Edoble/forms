<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('content'); ?>

<div style="display: flex; gap: 32px; align-items: flex-start;">
    <!-- Settings Sidebar -->
    <div style="width: 240px; flex-shrink: 0;">
        <div class="edf-card">
            <div class="edf-card-body" style="padding: 16px 0;">
                <a href="/settings" class="edf-nav-item" style="padding: 10px 24px; display: block; text-decoration: none; color: inherit;">Profile</a>
                <a href="/settings/workspaces" class="edf-nav-item active" style="padding: 10px 24px; display: block; text-decoration: none; color: inherit; background: var(--edf-hover); font-weight: 500;">Workspaces</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 1; max-width: 800px;">
        <div class="edf-card">
            <div class="edf-card-header d-flex justify-content-between align-center">
                <h2 class="edf-card-title">My Workspaces</h2>
            </div>
            
            <div class="edf-card-body">
                <?php if (empty($workspaces)): ?>
                    <p class="text-muted">You are not part of any workspaces yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php 
                            $workspaceService = new \App\Services\WorkspaceService();
                        ?>
                        <?php foreach ($workspaces as $w): ?>
                            <?php $members = $workspaceService->getWorkspaceMembers($w['id']); ?>
                            <div style="padding: 16px; border: 1px solid var(--edf-border); border-radius: 8px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                    <div>
                                        <h3 style="margin: 0 0 4px; font-size: 16px;"><?= e($w['name']) ?></h3>
                                        <div style="font-size: 13px; color: var(--edf-text-muted);">
                                            <?= \App\Core\Session::get('current_workspace_id') === $w['id'] ? '<span class="badge" style="background:#e0e7ff;color:#4338ca;padding:2px 6px;border-radius:4px;">Current Active</span>' : '' ?>
                                        </div>
                                    </div>
                                    <?php if (\App\Core\Session::get('current_workspace_id') !== $w['id']): ?>
                                    <form method="POST" action="/settings/workspaces/switch/<?= $w['id'] ?>" style="margin:0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="edf-btn edf-btn-secondary edf-btn-sm">Switch to Workspace</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="border-top: 1px solid var(--edf-border); padding-top: 12px;">
                                    <h4 style="font-size: 13px; margin: 0 0 8px; color: var(--edf-text-muted);">Members</h4>
                                    <ul style="list-style: none; padding: 0; margin: 0 0 16px;">
                                        <?php foreach ($members as $m): ?>
                                            <li style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; padding: 4px 0;">
                                                <span><?= e($m['user_id']) ?></span>
                                                <span class="badge" style="background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:4px; font-size: 12px; text-transform: capitalize;"><?= e($m['role']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    
                                    <form method="POST" action="/settings/workspaces/<?= $w['id'] ?>/invite" class="d-flex gap-2 align-center">
                                        <?= csrf_field() ?>
                                        <input type="email" name="email" class="edf-input edf-input-sm flex-1" placeholder="Invite by email" required>
                                        <select name="role" class="edf-input edf-input-sm" style="width: auto;">
                                            <option value="editor">Editor</option>
                                            <option value="viewer">Viewer</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <button type="submit" class="edf-btn edf-btn-secondary edf-btn-sm">Invite</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--edf-border);">
                    <h3 style="font-size: 16px; margin: 0 0 16px;">Create New Workspace</h3>
                    <form method="POST" action="/settings/workspaces">
                        <?= csrf_field() ?>
                        <div class="edf-form-group d-flex gap-2">
                            <input type="text" name="workspace_name" class="edf-input flex-1" placeholder="Workspace Name" required>
                            <button type="submit" class="edf-btn edf-btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>
