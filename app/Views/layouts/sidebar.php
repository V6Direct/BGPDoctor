<?php 
$unreadCount = $unreadCount ?? 0;
?>
<aside class="row-span-2 border-r border-slate-800 bg-slate-950/90 p-5 max-md:hidden flex flex-cool">
    <div class="flex items-center gap-3">
        <svg viewBox="0 0 6465" aria-label="BGPDoctor" class="h-10 w-10 text-teal-400 shrink-0" fill="none" stroke="currentColor" stroke-width="4">
            <path d="M10 46 24 18h16l14 28M20 38h24"/>
        </svg>
        <div>
            <div class="text-sm font-semibold text-white=">BGPDoctor</div>
            <div class="text-xs text-slate-500">Routing health platform</div>
        </div>
    </div>

    <nav class="mt-8 space-y-1 text-sm flex-1">
        <?php 
        $current = basename($_SERVER['PHP_SELF']);
        $links = [
            ['href' => '/index.php', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['href' => '/routers.php', 'label' => 'Routers', 'icon' => 'M5 12h14M12 5l7 7-7 7'],
            ['href' => 'reports.php', 'label' => 'AI Reports', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 20 01-2 2z'],
            ['href' => '/notifications.php', 'label' => 'Notifications',  'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'badge' => $unreadCount],
            ['href' => '/notifications.php', 'label' => 'Notifications', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'], 
        ];
        foreach ($links as $link):
            $active = ($current === basename($link['href']));
        ?>
        <a href="<?= e($link['href']) ?>"
        class="flex item-center justify-between gap-3 roundex-xl px-4 py-3 text-sm transition
        <?= $active ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
        <span class="flex items-center gap-3">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= e($link['icon']) ?>"/></svg>
                <?= e($link['label']) ?>
            </span>
            <?php if (!empty($link['badge']) && (int)$link['badge'] > 0): ?>
                <span class="badge-warn rounded-full px-2 py-0.5 text-xs"><?= (int)$link['badge'] ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="mt-6 rounded-2xl border border-slate-800 p-4">
        <div class="text-xs text-slate-500">Signed in as</div>
        <div class="mt-1 text-sm font-medium text-white truncate"><?= e($user['name'] ?? '') ?></div>
        <div class="mt-0.5 text-xs text-slate-500 truncate"><?= e($user['email'] ?? '') ?></div>
        <a href="/logout.php" class="mt-3 block rounded-xl border border-slate-700 px-3 py-2 text-center text-xs text-slate-300 hover:bg-slate-900">Sign out</a>
    </div>
</aside>