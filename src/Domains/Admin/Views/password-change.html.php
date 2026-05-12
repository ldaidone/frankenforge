<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="min-h-screen flex items-center justify-center px-6 py-12">

    <div class="w-full max-w-sm">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/assets/logo.jpg" alt="FrankenForge" class="w-16 h-16 rounded-xl shadow-lg mx-auto mb-4">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Change Password</h1>
            <?php if ($forced): ?>
                <p class="text-sm text-orange-500 mt-1">You must change your password before continuing.</p>
            <?php else: ?>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Update your password</p>
            <?php endif; ?>
        </div>

        <!-- Password Change Form -->
        <form method="POST" action="/dashboard/password" class="space-y-4">
            <?php if (!empty($error)): ?>
                <div class="rounded-lg px-4 py-3 text-sm bg-red-500/10 border border-red-500/30 text-red-400">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div>
                <label for="current_password" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Current Password</label>
                <input type="password" name="current_password" id="current_password"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       required <?= $forced ? 'autofocus' : '' ?>>
            </div>

            <div>
                <label for="new_password" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">New Password</label>
                <input type="password" name="new_password" id="new_password"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       minlength="8" required>
                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Minimum 8 characters</p>
            </div>

            <div>
                <label for="confirm_password" class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       required>
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-sm font-semibold transition">
                <?= $forced ? 'Change Password & Continue' : 'Update Password' ?>
            </button>

            <?php if (!$forced): ?>
                <a href="/dashboard/profile" class="block text-center text-sm text-zinc-500 dark:text-zinc-400 hover:text-orange-500 transition">
                    Cancel
                </a>
            <?php endif; ?>
        </form>

    </div>

</div>
