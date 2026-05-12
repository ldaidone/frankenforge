<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="min-h-screen flex items-center justify-center px-6 py-12">

    <div class="w-full max-w-sm">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/assets/logo.jpg" alt="FrankenForge" class="w-16 h-16 rounded-xl shadow-lg mx-auto mb-4">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">FrankenForge</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Admin Dashboard</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="/dashboard/login" class="space-y-4">
            <?php if (!empty($error)): ?>
                <div class="rounded-lg px-4 py-3 text-sm bg-red-500/10 border border-red-500/30 text-red-400">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div>
                <label for="email" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '') ?>"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="admin@example.com" required autofocus>
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Password</label>
                <input type="password" name="password" id="password"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="••••••••" required>
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-sm font-semibold transition">
                Sign In
            </button>
        </form>
        <p class="text-center text-xs text-zinc-400 dark:text-zinc-500 mt-6">
            <button type="reset" onclick="window.location.href='/'"
                    class="w-full py-2.5 bg-slate-800 text-slate-200 hover:bg-slate-700 rounded-lg text-sm font-semibold transition">
                Home
            </button>
        </p>
        <p class="text-center text-xs text-zinc-400 dark:text-zinc-500 mt-6">
            Default: <code class="px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 rounded">admin@frankenforge.local</code> / <code class="px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 rounded">changeme</code>
        </p>

    </div>

</div>
