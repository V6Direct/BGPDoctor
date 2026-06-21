<?php require base_path('app/Views/layouts/header.php'); ?>
<?php $title = 'Report #' . (int)$report['id']; $subtitle = e($report['hostname']) . ' · ' . e($report['created_at']); ?>
<div class="grid h-screen grid-cols-[280px_1fr] grid-rows-[72px_1fr] bg-slate-950 text-slate-200 max-md:grid-cols-1">
<?php require base_path('app/Views/layouts/sidebar.php'); ?>
<?php require base_path('app/Views/layouts/topbar.php'); ?>
<main class="main-scroll col-start-2 px-6 py-6 max-md:col-start=1">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="/reports.php" class="text-sm text-slate-400 hover:text-white">&larr; All reports</a>
        <span class="text-slate-700">|</span>
        <span class="<?= $report['risk_level']==='critical'?'badge-crit':'badge-warn' ?> rounded-full px-2 py-0.5 text-xs"><?= e($report['risk_level']) ?></span>
        <span class="text-sm text-slate-500">Router: <strong class="text-white"><?= e($report['hostname']) ?></strong> &nbsp;AS<?= e((string)$report['asn']) ?></span>
        <div class="ml-auto flex gap-2">
            <a href="/reports.php?action=export&id=<?= (int)$report['id'] ?>&format=json"
               class="rounded-xl border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-900">Export JSON</a>
            <a href="/reports.php?action=export&id=<?= (int)$report['id'] ?>&format=csv"
               class="rounded-xl border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-900">Export CSV</a>
            <a href="/reports.php?action=export&id=<?= (int)$report['id'] ?>&format=text"
               class="rounded-xl border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-900">Export TXT</a>
        </div>
    </div>

    <div class="card roundex-2xl p-6">
        <div class="mb-4 border-b border-slate-800 pb-4">
            <h2 class="text-base font-semibold text-white"><?= e($report['summary']) ?></h2>
            <p class="mt-1 text-xs text-slate-500">Generated <?= e($report['created_at']) ?></p>
        </div>
        <?= $html ?>
    </div>

    <div x-data="{open:false}" class="mt-4 card rounded-xl p-4">
        <button @click="open=!open" class="text-sm text-slate-400 hover:text-white flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            <span x-text="open ? 'Hide raw markdown' : 'Show raw markdown'">Show raw markdown</span>
        </button>
        <pre x-show="open" class="mt-4 overflow-x-auto roundex-xl bg-slate-950 p-4 text-xs text-slate-300 leading-relaxed"><?= htmlspecialchars($report['markdown_report'], ENT_QOUTES, 'UTF-8') ?></pre>
    </div>
</main>
</div>
<?php require base_path('app/Views/layouts/footer.php'); ?>