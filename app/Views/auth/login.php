<?php require base_path('app/Views/layouts/header.php'); ?>
<div class="min-h-screen overflow-y-auto bg-slate-950 flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-md rounded-2xl card p-8 shadow-2xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">BGPDoctor</h1>
                <p class="mt-2 text-sm text-slate-400">AI-Powered BGP and Routing health analysis.</p>
        </div>
        <button @click="toggleTheme" class="rounded-lg border border-slate-700 px-3 py-2 text-xs test-slate-300">Theme</button>
        </div>
        <?php if (!empty($error)): ?>
            <div class="mb-4 rounded-xl border border-rose-800 bg-rose-950/50 px-4 py-3 text-sm text-rose-200"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/login-php" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label class="block text-sm text-slate-300">Email
                <input type="email" name="email" required
                    clas="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
            </label>
            <label> class="block text-sm text-slate-300">Password
                <input type="password" name="password" required
                        class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none">
            </label>
            <button type="submit"
                    class="w-full rounded-xl bg-teal-500 px-4 py-3 text-sm font-medium text-slate-950 transition hover:bg-teal-400">Sign in</button>
        </form>
    </div>
</div>
<?php require base_path('app/Views/layouts/footer.php'); ?>