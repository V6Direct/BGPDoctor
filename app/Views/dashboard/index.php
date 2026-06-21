<?php require base_path('app/Views/layouts/header.php'); ?>
<?php $title = 'Dashboard'; $subtitle = 'Live BGP telemetry, AI analysis, and routing safety checks.'; ?>
<div class="grid h-screen grid-cols-[280px_1fr] grid-rows-[72px_1fr] bg-slate-950 text-slate-200 max-md:grid-cols-1">
<?php require base_path('app/Views/layouts/sidebar.php'); ?>
<?php require base_path('app/Views/layouts/topbar.php'); ?>

<main id="content" class="main-scroll col-start-2 space-y-6 px-6 py-6 max-md:col-start-1">

    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <?php if (!empty($_GET['success'])): ?>
    <div class="rounded-xl border border-teal-800 bg-teal-950/50 px-4 py-3 text-sm text-teal-200">Action completed successfully.</div>
    <?php endif; ?>

    <?php if (empty($routers)): ?>
    <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/40 p-8 text-center">
        <div class="text-lg font-semibold text-white mb-2">No routers connected yet</div>
        <p class="text-sm text-slate-400 mb-4">Deploy the BGPDoctor agent and point it at <code class="text-teal-400"><?= e($_SERVER['HTTP_HOST'] ?? 'bgp.core01.eu') ?>/api/ingest.php</code>.</p>
        <a href="/routers.php?action=create" class="inline-block rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-medium text-slate-950 hover:bg-teal-400">+ Manually register a router</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($routers)): ?>

    <!-- KPI cards -->
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <?php $latest = $snapshots[0] ?? null; ?>
        <div class="card rounded-2xl p-5"><div class="text-xs uppercase tracking-widest text-slate-500">Routers</div><div class="num mt-3 text-3xl font-semibold text-white"><?= count($routers) ?></div><div class="mt-1 text-sm text-slate-500">Monitored nodes</div></div>
        <div class="card rounded-2xl p-5"><div class="text-xs uppercase tracking-widest text-slate-500">BGP peers</div><div class="num mt-3 text-3xl font-semibold text-white"><?= count($peers) ?></div><div class="mt-1 text-sm text-slate-500"><?= $latest ? 'Latest snapshot' : 'No data yet' ?></div></div>
        <div class="card rounded-2xl p-5"><div class="text-xs uppercase tracking-widest text-slate-500">Established</div><div class="num mt-3 text-3xl font-semibold text-white"><?= count(array_filter($peers, fn($p)=>$p['state']==='Established')) ?></div><div class="mt-1 text-sm text-slate-500">Healthy sessions</div></div>
        <div class="card rounded-2xl p-5"><div class="text-xs uppercase tracking-widest text-slate-500">IPv6 prefixes</div><div class="num mt-3 text-3xl font-semibold text-white"><?= $latest ? number_format((int)$latest['ipv6_prefixes']) : '—' ?></div><div class="mt-1 text-sm text-slate-500"><?= $latest ? 'Kernel routes' : 'No snapshot' ?></div></div>
        <div class="card rounded-2xl p-5"><div class="text-xs uppercase tracking-widest text-slate-500">RPKI invalid</div><div class="num mt-3 text-3xl font-semibold text-white"><?= $latest ? (int)$latest['rpki_invalid'] : '—' ?></div><div class="mt-1 text-sm text-slate-500"><?= $latest ? 'Routes to fix' : 'No snapshot' ?></div></div>
    </section>

    <!-- Chart + latest report -->
    <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <div class="card rounded-2xl p-5">
            <div class="mb-4 flex items-center justify-between">
                <div><h2 class="text-lg font-semibold text-white">Health trends</h2><p class="text-sm text-slate-500">CPU &middot; RAM &middot; latency &middot; leak score</p></div>
                <span class="rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-400">Auto-refresh 30s</span>
            </div>
            <?php if (count($snapshots) >= 2): ?>
            <canvas id="healthChart" height="110"></canvas>
            <?php else: ?>
            <div class="rounded-xl border border-dashed border-slate-700 p-6 text-center text-sm text-slate-500">
                <?= empty($snapshots) ? 'No snapshots yet — waiting for agent check-in.' : 'Trends appear after 2+ snapshots are received. One received so far.' ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Latest AI report</h2>
                <a href="/reports.php" class="text-xs text-teal-400 hover:underline">View all &rarr;</a>
            </div>
            <?php $latestReport = $reports[0] ?? null; ?>
            <?php if ($latestReport): ?>
                <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium text-white truncate"><?= e($latestReport['summary']) ?></span>
                        <span class="<?= $latestReport['risk_level']==='critical'?'badge-crit':'badge-warn' ?> rounded-full px-2 py-1 text-xs shrink-0"><?= e($latestReport['risk_level']) ?></span>
                    </div>
                    <div class="mt-3 text-xs text-slate-400 line-clamp-5 whitespace-pre-line"><?= e(substr($latestReport['markdown_report'],0,400)) ?>&hellip;</div>
                    <a href="/reports.php?action=show&id=<?= (int)$latestReport['id'] ?>" class="mt-3 block text-xs text-teal-400 hover:underline">Read full report &rarr;</a>
                </div>
            <?php else: ?>
                <div class="rounded-xl border border-dashed border-slate-700 p-5 text-sm text-slate-500">
                    No AI report yet.<br>
                    <?php if (!empty($snapshots)): ?>
                    <button onclick="triggerReport(<?= (int)($router['id']??0) ?>, this)"
                            class="mt-3 inline-flex items-center gap-2 rounded-xl bg-teal-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-teal-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Run AI report now
                    </button>
                    <?php else: ?>
                    <span class="text-xs">Send a snapshot from an agent first.</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    </section class="card rounded-2xl p-5">
    <div class="mb-4 flex-items-center justify-between">
        <h2 class="text-lg font-semibold text-white">Routers</h2>
        <a href="/routers.php?action=create" class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-teal-400 transition">+ Add routers</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-slate-500"><tr>
                <th class="pb-3">Hostname</th><th class="pb-3">ASN</th><th class="pb-3">Group</th>
                <th class="pb-3">Peers (est/total)</th><th class="pb-3">Last seen</th><th class="pb-3">Actions</th>
        </tr></thead>
        <tbody class="divided-y divided-slate-800">
        <?php foreach ($routers as $item): ?>
            <tr class="hover:bg-slate-900/40">
                <td class="py-3 font-medium text-white"><?= e($item['hostnames']) ?></td>
                <td class="py-3 num text-slate-300">AS<?= e((string)$item['asn']) ?></td>
                <td class="py-3 text-slate-400"><?= e($item['group_name'] ??'-') ?></td>
                <td class="py-3 num">
                    <span class="<?= ((int)$item['established_count']>0)?'text-teal-400':'text-rose-400' ?>"><?= (int)$item['established_count'] ?></span>
                    <span class="text-slate-500">/ <?= (int)$item['peer_count'] ?></span>
                </td>
                <td class="py-3 text-slate-500 text-xs"><?= e((string)($item['last_seen']??'Never')) ?></td>
                <td class="py-3">
                    <div class="flex items-center gap-3">
                        <a href="/routers.php?action=edit&id=<?= (int)$item['id'] ?>" class="text-xs text-slate-400 hover:text-white">Edit</a>
                        <button onclick="triggerReport(<?= (int)$item['id'] ?>, this)"
                            data-router-id="<?= (int)$item['id'] ?>"
                            class="ai-report-btn inline-flex items-center gap-1.5 rounded-lg border border-teal-700 px-2.5 py-1 text-teal-400 hover:bg-teal-950 transition"
                            title="Run AI report for <?= e($item['hostname']) ?>">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            AI report
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

    <section class="card rounded-2xl p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Notifications
                <?php if ($unreadCount > 0): ?>
                    <span class="badge-warn ml-2 rounded-full px-2 py-0.5 text-xs"><?= $unreadCount ?></span>
                <?php endif; ?>
            </h2>
            <form method="post" action="/notifications.php">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
                <input type="hidden" name="_action" value="clear_all">
                <button type="submit" class="text-xs text-slate-500 hover:text-rose-400 transition">Clear all</button>
            </form>
        </div>
        <?php if (empty($notifications)): ?>
        <div class="rounded-xl border border-dashed border-slate-700 p-5 text-sm text-slate-500">No notifications yet.</div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach (array_slice($notifications,0,6) as $note): ?>
            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-medium text-white truncate"><?= e($note['message']) ?></div>
                    <span class="<?= $note['severity']==='critical'?'badge-crit':($note['severity']==='warning'?'badge-warn':'badge-ok') ?> rounded-full px-2 py-0.5 text-xs shrink-0"><?= e($note['severity']) ?></span>
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <div class="text-xs text-slate-500"><?= e($note['hostname']??'global') ?> &middot; <?= e($note['created_at']) ?></div>
                    <?php if (!$note['is_read']): ?>
                    <form method="post" action="/notifications.php">
                        <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
                        <input type="hidden" name="_action" value="mark_read">
                        <input type="hidden" name="_id" value="<?= (int)$note['id'] ?>">
                        <button class="text-xs text-teal-500 hover:underline">Mark read</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

<?php if (!empty($peers)): ?>
    <section class="card roundex-2xl p-5">
        <div class="mb-4 flex-items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">BGP peer status</h2>
                <?php if (!empty($snapshots[0])): ?>
                <p class="text-xs text-slate-500">Snapshot from <?= e($snapshots[0]['created_at']) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($reports)): ?>
                <div class="flex gap-2">
                    <a href="/reports.php?action=export&id=<?= (int)($reports[0]['id']??0) ?>&format=json"
                    class="rounded-xl border border-slate-700 px-3 py-2 text-xs text-slate-300 hover:bg-slate-900">Export JSON</a>
                    <a href="/reports.php?action=export&id=<?= (int)($reports[0]['id']??0) ?>&format=csv"
                    class="rounded-xl border border-slate-700 px-3 py-2 text-xs text-slate-300 hover:bg-slate-900">Export CVS</a>
                </div>
                <?php endif; ?>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($peers as $peer): ?>
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
                    <div class="flex items-start justify-betweem gap-2">
                        <div>
                            <div class="text-sm font-semibold text-white"><?= e($peer['name']) ?></div>
                            <div class="mt-0.5 text-xs text-slate-500">
                                <?= $peer['is_ipv6']?'IPv6':'IPv4' ?>
                                <?php if (!empty($peer['pathvector_profile']) && $peer['pathvector_profile'] !== 'default'): ?>
                                    &middot; <?= e($peer['pathvector_profile']) ?>
                                    <?php endif; ?>
                            </div>
                            <?php if (!empty($peer['latency_ms'])): ?>
                                <div class="mt-0.5 text-xs text-slate-600"><?= number_format((float)$peer['latency_ms'],2) ?> ms</div>
                                <?php endif; ?>
                            </div>
                            <span class="<?= $peer['state']==='Established'?'bagde-ok':'badge-warn' ?> rounded-full px-2 py-0.5 text-xs shrink-0"><?= e($peer['state']) ?></span>
                        </div>
                            <div class="text-slate-500">Received</div>
                            <div class="num text-white"><?= number_format((int)$peers['prefixes_received']) ?></div>
                    </div>
                        <div class="text-slate-500">Advertised</div>
                        <div class="num text-white"><?= number_format((int)$peer['prefixes_advertised']) ?></div>
                    </div>
                        <div class="text-slate-500">RPKI</div>
                        <div class="<?= $peer['rpki_state']==='valid'?'text-teal-400':($peer['rpki_state']==='invalid'?'text-rose-400':'text-slate-400') ?>"><?= e($peer['rpki_state']) ?></div>
                    </div>
                    <div>
                        <div class="text-slate-500">Flaps</div>
                        <div class="num <?= ((int)$peer['flap_count']>2)?'text-rose-400':'text-white' ?>"><?= (int)$peer['flap_count'] ?></div>
                    </div>
                </div>
                <?php if (!empty($peer['prefix_limit']) && (int)$peer['prefix_limit'] > 0): ?>
                <?php
                    $pct = round(((int)$peer['prefixes_received'] / (int)$peer['prefix_limit']) * 100);
                    $pct = min($pct, 100);
                ?>
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                        <span>Prefix limit</span>
                        <span><?= number_format((int)$peer['prefix_limit']) ?> (<?= $pct ?>%)</span>
                </div>
                <div class="h-1.5 rounded-full bg-slate-800">
                        <div class="h-1.5 rounded-full <?= $pct>=90?'bg-rose-500':($pct>=70?'bg-amber-500':'bg-teal-500') ?>" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div> 
</section>

<?php elseif (!empty($routers)): ?>
<section class="card roundex-2xl p-5">
    <h2 class="text-lg font-semibold text-white mb-2">BGP peer status</h2>
    <div class="rounded-xl border border-dashed border-slate-700 p-5 text-sm text-slate-500">
        No snapshot received yet. The agent will populate peer data on its first check-in.
    </div>
</section>
<?php endif; ?>
<?php endif; // !empty($routers) ?>
</main>
</div>

<?php if (count($snapshots) >= 2): ?>
<?php
$chartSnaps = array_reverse($snapshots);
$chartLabels = array_map(function($r) {
    $ts = strtotime($r['created_at']);
    return $ts ? date('H:i', $ts) : $r['created_at'];
}, $chartSnaps);
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('healthChart');
    if (!ctx) return;

    const labels   = <?= json_encode($chartLabels) ?>;
    const cpuData  = <?= json_encode(array_map(fn($r) => round((float)($r['cpu_percent']  ?? 0), 2), $chartSnaps)) ?>;
    const ramData  = <?= json_encode(array_map(fn($r) => round((float)($r['ram_percent']  ?? 0), 2), $chartSnaps)) ?>;
    const latData  = <?= json_encode(array_map(fn($r) => round((float)($r['latency_ms']   ?? 0), 2), $chartSnaps)) ?>;
    const leakData = <?= json_encode(array_map(fn($r) => round((float)($r['route_leak_score'] ?? 0), 2), $chartSnaps)) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'CPU %',      data: cpuData,  borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,.12)', tension: .35, fill: true,  pointRadius: 3 },
                { label: 'RAM %',      data: ramData,  borderColor: '#60a5fa', backgroundColor: 'transparent',          tension: .35, fill: false, pointRadius: 3 },
                { label: 'Latency ms', data: latData,  borderColor: '#f59e0b', backgroundColor: 'transparent',          tension: .35, fill: false, pointRadius: 3 },
                { label: 'Leak score', data: leakData, borderColor: '#f43f5e', backgroundColor: 'transparent',          tension: .35, fill: false, pointRadius: 3 },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: '#cbd5e1', boxWidth: 12 } },
                tooltip: { backgroundColor: '#0f172a', borderColor: '#334155', borderWidth: 1 }
            },
            scales: {
                x: { ticks: { color: '#64748b', maxTicksLimit: 8 }, grid: { color: 'rgba(100,116,139,.1)' } },
                y: { ticks: { color: '#64748b' },                   grid: { color: 'rgba(100,116,139,.1)' } },
            },
        },
    });
});
</script>
<?php endif; ?>

<script>
const CSRF = <?= json_encode($csrf) ?>;
function showToast(msg, ok = true) {
    const container = document.getElementById('toastContainer');
    const el = document.createdElement('div');
    el.className = [
        'pointer-events-auto rounded-xl px-4 py-3 text-sm shadow-xl',
        ok ? 'bg-teal-900 border border-teal-700 text-teal-100'
           : 'bg-rose-950 border border-rose-700 text-rose-200',
    ].join(' ');
    el.textContent = msg;
    container.appendChild(el);
    setTimeout(() => el.remove(), 6000);
}

async function triggerReport(routerId, btn) {
    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Running…';
    try {
        const res = await fetch('/api/analyze.php', {
            method: 'POST'
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ router_id: routerID, _csrf: CSRF}),
        });
        const data = await res.json();
        if (data.ok) {
            showToast('\u2713 Report ready: ' + data.summary);
            setTimeout(() => { window.location.href = data.redirect_url; }, 1200);
            } else {
                showToast('\u2717 ' + (data.message ?? 'Unknown error'), false);
                btn.disabled = false;
                btn.innerHTML = original;
            }
        } catch (err) {
            showToast('\u2717 Network error: ' + err.message, false);
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }
    let refreshTimer = setTimeout(() => location.reload(), 30000);
    document.querySelectorAll('.ai-report-btn').forEach(btn => {
        btn.addEventListener('click', () => clearTimeout(refreshTimer));
    });
</script>

<?php require base_path('app/Views/layouts/footer.php'); ?>