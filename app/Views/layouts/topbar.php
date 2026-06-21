<?php
$subtitle = $subtitle ?? '';
$theme = $theme ?? 'dark';
?>
<header class="col-start-2 flex items-center justify-between border-b border-slate-800 bg-slate-950/80 px-6 backdrop-blur max-md:col-start-1">
    <div>
        <h1 class="text-xl font-semibold text-white><?= e($title) ?></h1>
        <?php if ($subtitle): ?><p class="text-sm text-slate-500><?= e($subtitle) ?></p><?php endif;?>
    </div>
    <div class="flex items-center gap-3">
        <button id="themeToggle"
        class="rounded-xl border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 transition"
        title="Toggle theme">
        <span id="themeIcon"><?= $theme === 'dark' ? '☀️' : '🌙' ?></span>
        </button>
    </div>
</header>
<script>
(function(){
    const btn  = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const html = document.documentElement;
    let theme  = <?= json_encode($theme) ?>;
    html.setAttribute('data-theme', theme);
    btn.addEventListener('click', async () => {
        theme = theme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', theme);
        icon.textContent = theme === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
        await fetch('/api/theme.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({theme})
        });
    });
})();
</script>