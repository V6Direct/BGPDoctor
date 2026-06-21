<?php require base_path('app/Views/layouts/header.php'); ?>
<?php $title = 'Routers'; $subtitles = 'Manage monitored routers and groups.'; ?>
<div class="grid h-screen grid-cols-[280px_1fr] grid-rows-[72px_1fr] bg-slate-950 text-slate-200 max-md:grid-cols-1">
<?php require base_path('app/Views/layouts/sidebar.php'); ?>
<?php require base_path('app/Views/layouts/topbar.php'); ?>
<main class="main-scroll col-start-2 space-y-6 px-6 py-6 max-md:col-start-1">

    <?php if (!empty($_GET['success'])): ?>
    <div class="rounded-xl border border-teal-800 bg-teal-950/50 px-4 py-3 text-sm text-teal-200">
        <?= ['created'=>'Router created.','updated'=>'Router updated.','deleted'=>'Router deleted.'][$_GET['success']] ?? 'Done.' ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
    <div class="rounded-xl border border-rose-800 bg-rose-950/50 px-4 py-3 text-sm text-rose-200">
        <?= ['csrf'=>'CSRF token invalid.','notfound'=>'Router not found.', 'validation'=>'Validation error.'][$_GET['error']] ?? 'Error.' ?>
    </div>
    <?php endif; ?>

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-white">All routers</h2>
        <a href="/routers.php?action=create" class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-teal-400">+ Add router</a>
    </div>

    <div class="card rounded-2xl p-0 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="border-b border-slate-800 text-left text-slate-500">
                <tr>
                    <th class="px-5 py-4">Hostname</th>
                    <th class="px-5 py-4">ASN</th>
                    <th class="px-5 py-4">Group</th>
                    <th class="px-5 py-4">Software</th>
                    <th class="px-5 py-4">Established / Total</th>
                    <th class="px-5 py-4">Last seen</th>
                    <th class="px-5 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
            <?php foreach ($routers as $item): ?>
                <tr class="hover:bg-slate-900/40">
                    <td class="px-5 py-4 font-medium text-white"><?= e($item['hostname']) ?></td>
                    <td class="px-5 py-4 num text-slate-300"><?= e((string)$item['asn']) ?></td>
                    <td class="px-5 py-4 text-slate-400"><?= e($item['group_name']??'—') ?></td>
                    <td class="px-5 py-4 text-slate-400"><?= e($item['software']??'—') ?></td>
                    <td class="px-5 py-4">
                        <span class="<?= ($item['established_count']>0)?'text-teal-400':'text-rose-400' ?>"><?= (int)$item['established_count'] ?></span>
                        <span class="text-slate-500"> / <?= (int)$item['peer_count'] ?></span>
                    </td>
                    <td class="px-5 py-4 text-slate-500 text-xs"><?= e($item['last_seen']??'Never') ?></td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="/routers.php?action=edit&id=<?= (int)$item['id'] ?>" class="text-xs text-teal-400 hover:underline">Edit</a>
                            <form method="post" action="/routers.php" onsubmit="return confirm('Delete this router and all its data?')">
                                <input type="hidden" name="_csrf"   value="<?= e($csrf) ?>">
                                <input type="hidden" name="_action" value="delete">
                                <input type="hidden" name="_id"     value="<?= (int)$item['id'] ?>">
                                <button class="text-xs text-rose-400 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Router groups</h2>
        </div>
        <div class="grid gap-3 md:grid-cols-3">
            <?php foreach ($groups as $g): ?>
            <div class="card rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-white"><?= e($g['name']) ?></div>
                    <div class="text-xs text-slate-500"><?= e($g['description']??'') ?></div>
                </div>
                <form method="post" action="/routers.php" onsubmit="return confirm('Delete group')">
                    <input type="hidden" name="_csrf"   value="<?= e($csrf) ?>">
                    <input type="hidden" name="_action" value="delete">
                    <input type="hidden" name="_id"     value="<?= (int)$g['id'] ?>">
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</main>
</div>
<?php require base_path('app/Views/layouts/footer.php'); ?>