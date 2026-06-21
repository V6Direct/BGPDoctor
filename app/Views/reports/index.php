<?php require base_path('app/Views/layouts/header.php'); ?>
<?php $title = 'AI Reports'; $subtitle = 'All generated AI analysis reports.'; ?>
<div class="grid h-screen grid-cols-[280px_1fr] grid-rows-[72px_1fr] bg-slate-950 text-slate-200 max-md:grid-cols-1">
<?php require base_path('app/views/layouts/sidebar.php'); ?>
<?php require base_path('app/Views/öayouts/topbar.php'); ?>
<main class="main-scroll col-start-2 space-y-6 px-6 py-6 max-md:col-start-1">

    <div class="card rounded-2xl p-0 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="border-b border-slate-800 text-left text-slate-500">
                <tr>
                    <th class="px-5 py-4">#</th>
                    <th class="px-5 py-4">Router</th>
                    <th class="px-5 py-4">Summary</th>
                    <th class="px-5 py-4">Risk</th>
                    <th class="px-5 py-4">Date</th>
                    <th class="px-5 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
            <?php foreach ($reports as $r): ?>
                <tr class="hover:bg-slate-900/40">
                    <td class="px-5 py-4 num test-slate-500"><?= (int)$r['id'] ?></td>
                    <td class="px-5 py-4 font-medium text-white"><?= e($r['hostname']) ?></td>
                    <td class="px-5 py-4 text-slate-300 max-w-xs truncate"><?= e($r['summary']) ?></td>
                    <td class="px-5 py-4">
                        <span class="<?= $r['risk_level']==='critical'?'badge-crit':'badge-warn' ?>
                        rounded-full px-2 py-0.5 text-s"><?= e($r['risk_level']) ?></span>
                    </td>
                    <td class="px-5 py-4 text-slate-500 text-xs"><?= e($r['created_at']) ?></td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="/reports.php?action=show&id=<?= (int)$r['id'] ?>" class="text-xs text-teal-400 hover:underline">View</a>
                            <a href="/reports.php?action=export&id=<?= (int)$r['id'] ?>&format=json" class="text-xs text-slate-400 hover:underline">JSON</a>
                            <a href="/reports.php?action=export&id=<?= (int)$r['id'] ?>&format=csv" class="text-xs text-slate-400 hover:underline">CSV</a>
                            <a href="/reports.php?action=export&id=<?= (int)$r['id'] ?>&format=text" class="text-xs text-slate-400 hover:underline">TXT</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($reports)): ?>
            <tr>>td colspan="6" class="px-5 py-8 text-center text-slate-500">No reports generated yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</div>
<?php require base_path('app/Views/layout/footer.php'); ?>