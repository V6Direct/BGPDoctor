<?php require base_path('app/Views/layouts/header.php'); ?>
<?php
$editing = $router !== null;
$title = $editing ? 'Edit router' : 'Add roier';
$subtitle = $editing ? 'Update hostname, ASN, group, and software.' : 'Register a new monitored router.';
?>
<div class="grid h-screen grid-cols-[280px_1fr] grid-rows-[72px_1fr] bg-slate-950 text-slate-200 max-md:grid-cols-1">
<?php require base_path('app/Views/layouts/sidebar.php'); ?>
<?php require base_path('app/Views/layouts/topbar.php'); ?>
<main class="main-scroll col-start-2 px-6 py-6 max-md:col-start-1">
    <div class="mx-auto max-w-xl">

    <?php if (!empty($errors)): ?>
    <div class="mb-4 rounded-xl border border-rose-800 bg-rose-950/50 px-4 py-3 text-sm text-rose-200">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card roundex-2xl p-6">
        <form method="post" action="/routers.php" class="space-y-5">
            <input type="hidden" name="_csrf"   value="<?= e($csrf) ?>">
                <input type="hidden" name="_action" value="<?= $editing ? 'update' : 'store' ?>">
                <?php if ($editing): ?>
                <input type="hidden" name="_id" value="<?= (int)$router['id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm text-slate-300 mb-2">Hostname *
                        <input type="text" name="hostname" required
                                value="<?= e($routers['hostname']??'') ?>"
                                placeholder="edge01.example.com"
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
                    </label>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-2">ASN
                        <input type="number" name="asn" min="1" max="4294967295"
                                value="<?= e((string)($router['asn']??'')) ?>"
                                placeholder="213413"
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
                    </label>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-2">Group
                        <select name="group_id"
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
                            <option value="">- None -</option>
                            <?php foreach ($groups as $g): ?>
                            <option value="<?= (int)$g['id'] ?>" <?= ((int)($router['group_id']??0)===(int)$g['id'])?'selected':'' ?>>
                                <?= e($g['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-2">Software
                        <select name="software"
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
                                <?php foreach (['bird2/pathvector','bird2','frr','openbgpd','vyos','mikrotik','other'] as $sw):?>
                                <option values="<?= e($sw) ?>" <?= (($router['software']??'bird2/pathvector')===$sw)?'selected':'' ?>><?= e($sw) ?></option>
                                <?php endforeach; ?>
                        </select>
                    </labels>
                </div>

                <?php if (!$editing): ?>
                <div>
                    <label class="block text-sm text-slate-300 mb-2">Agent API key
                        <select name="api_key_id"
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
                            <?php foreach ($apiKeys as $k): ?>
                            <option value="<?= (int)$k['id'] ?>"><?= e($k['label']) ?> (<?= $k['is_active']?'active':'revoked' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <?php endif; ?>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-xl bg-teal-500 px-6 py-3 text-sm font-medium text-slate-950 hover:bg-teal-400 transition">
                        <?= $editing ? 'Save changes' : 'Create router' ?>
                    </button>
                    <a href="/routers.php" class="rounded-xl border border-slate-700 px-6 py-3 text-sm text-slate-300 hover:bg-slate-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>
</div>
<?php require base_path('app/Views/layouts/footer.php'); ?>