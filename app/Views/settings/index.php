<?php require base_path('app/Views/layouts/header.php'); ?>
<?php $title = 'Settings'; $subtitle = 'API keys, account password, and preferences.'; ?>
<div class="grid h-screen grid-cols-[280px_1fr] grid-rows-[72px_1fr] bg-slate-950 text-slate-200 max-md:grid-cols-1">
<?php require base_path('app/Views/layouts/sidebar.php'); ?>
<?php require base_path('app/Views/layouts/topbar.php'); ?>
<main class="main-scroll col-start-2 space-y-6 px-6 py-6 max-md:col-start-1">

    <?php if (!empty($success)): ?>
    <div class="rounded-xl border border-teal-800 bg-teal-950/50 px-4 py-3 text-sm text-teal-200">
        <?= [
            'password_changed' => 'Password changed successfully.',
            'key_created' => 'API key created.',
            'key_revoked' => 'API key revoked.',
        ][$success] ?? 'Done.' ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="rounded-xl border border-rose-800 bg-rose-950/50 px-4 py-3 text-sm text-rose-200">
        <?= [
            'csrf' => 'CSRF token invalid.',
            'wrong_password' => 'Current password is incorrect.',
            'too_sort' => 'New password must be at least 8 characters.',
            'mismatch' => 'Passwords do not match.',
        ]['$error'] ?? 'An error occured.' ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_api_key'])): ?>
    <div class="rounded-xl border border-amber-800 bg-amber-950/50 px-4 py-3 text-sm">
        <div class="font-semibold text-amber-300">New API key - copy it now, it will not be shown again:</div>
        <code class="mt-2 block break-all rounded-lg bg-slate-950 px-4 py-3 text-teal-300 text-xs"><?= e($_SESSION['flash_api_key']) ?></code>
    </div>
    <?php unset($_SESSION['flash_api_key']); ?>
    <?php endif; ?>

    <section class="card rounded-2xl p-6">
        <h2 class="text-base font-semibold text-white mb-4">Change password</h2>
        <form method="post" action="/settings.php" class="space-y-4 max-w-sm">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="_action" value="update_password">
            <label class="block text-sm text-slate-300">Current password
                <input type="password" name="current_password" required
                        class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
            </label>
            <label class="block text-sm text-slate-300">Confirm new password
                <input type="password" name="new_password" required minlength="8"
                        class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
            </label>
            <button type="submit" class="rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-medium text-slate-950 hover:bg-teal-400">Update password</button>
        </form>
    </section>

    <section class="card rounded-2xl p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-white">Agent API keys</h2>
        </div>

        <div class="overflow-x-auto mb-6">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500 border-b border-slate-800">
                    <tr>
                        <th class="pb-3">Label</th>
                        <th class="pb-3">Key (trunacated)</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Created</th>
                        <th class="pb-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divided-y divided-slate-800">
                <?php foreach ($apiKeys as $k): ?>
                     <tr class="hover:bg-slate-900/30">
                        <td class="py-3 text-white"><?= e($k['label']) ?></td>
                        <td class="py-3 font-mono text-xs text-slate-400"><?= e(substr($k['api_key'],0,20)) ?>…</td>
                        <td class="py-3">
                            <span class="<?= $k['is_active']?'badge-ok':'badge-warn' ?> rounded-full px-2 py-0.5 text-xs"><?= $k['is_active']?'active':'revoked' ?></span>
                        </td>
                        <td class="py-3 text-xs text-slate-500"><?= e($k['last_used_at']??'Never') ?></td>
                        <td class="py-3 text-xs text-slate-500"><?= e($k['created_at']) ?></td>
                        <td class="py-3">
                            <?php if ($k['is_active']): ?>
                            <form method="post" action="/settings.php" onsubmit="return confirm('Revoke this key?')">
                                <input type="hidden" name="_csrf"   value="<?= e($csrf) ?>">
                                <input type="hidden" name="_action" value="revoke_api_key">
                                <input type="hidden" name="_id"     value="<?= (int)$k['id'] ?>">
                                <button class="text-xs text-rose-400 hover:underline">Revoke</button>
                            </form>
                            <?php else: ?>
                            <span class="text-xs text-slate-600">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form method="post" action="/settings.php" class="flex items-end gap-3">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="_action" value="create_api_key">
            <label class="block text-sm text-slate-300 flex-1">Key label
                <input type="text" name="label" required placeholder="e.g edge-fleet-berlin"
                        class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
            </label>
            <button type="submit" class="roundex-xl bg-teal-500 px-5 py-3 text-sm font-medium text-slate-950 hover:bg-teal-400 shrink-0">Generate key</button>
        </form>
    </section>

</main>
</div>
<?php require base_path('app/Views/layouts/footer.php'); ?>