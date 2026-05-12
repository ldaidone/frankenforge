<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Profile</h1>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $message): ?>
            <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-<?= $type === 'success' ? 'green' : 'red' ?>-500/10 border border-<?= $type === 'success' ? 'green' : 'red' ?>-500/30 text-<?= $type === 'success' ? 'green' : 'red' ?>-400">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Profile Form -->
    <form method="POST" action="/dashboard/profile" class="max-w-lg space-y-4">
        <div>
            <label for="name" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Name</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user->name) ?>"
                   class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                   required>
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user->email) ?>"
                   class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                   required>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="px-6 py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-sm font-semibold transition">
                Save Changes
            </button>
        </div>
    </form>

    <!-- Password Change Link -->
    <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800 max-w-lg">
        <a href="/dashboard/password" class="text-sm text-orange-500 hover:text-orange-400 underline underline-offset-4">
            Change password →
        </a>
    </div>

</div>
