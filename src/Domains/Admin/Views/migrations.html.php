<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Migrations</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1"><?= count($migrations) ?> total, <?= $pending ?> pending</p>
    </div>

    <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $message): ?>
            <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-<?= $type === 'success' ? 'green' : 'red' ?>-500/10 border border-<?= $type === 'success' ? 'green' : 'red' ?>-500/30 text-<?= $type === 'success' ? 'green' : 'red' ?>-400">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Migration List -->
    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-left text-xs text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <th class="px-4 py-3 font-medium w-8"></th>
                    <th class="px-4 py-3 font-medium">Migration</th>
                    <th class="px-4 py-3 font-medium">Applied</th>
                    <th class="px-4 py-3 font-medium w-24 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($migrations as $m): ?>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <td class="px-4 py-3">
                            <?php if ($m['applied']): ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500/20 text-green-400 text-xs">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-zinc-500/20 text-zinc-400 text-xs">
                                    <i class="fa-solid fa-minus"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">
                            <?= htmlspecialchars($m['file']) ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-500">
                            <?= $m['applied'] ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($m['applied_at']))) : '<span class="italic text-zinc-400">pending</span>' ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php if ($m['applied']): ?>
                                <form method="POST" action="/dashboard/migrations/run" class="inline">
                                    <input type="hidden" name="action" value="down">
                                    <input type="hidden" name="migration" value="<?= htmlspecialchars($m['file']) ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 transition">
                                        Rollback
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="/dashboard/migrations/run" class="inline">
                                    <input type="hidden" name="action" value="up">
                                    <input type="hidden" name="migration" value="<?= htmlspecialchars($m['file']) ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-600 hover:bg-orange-500 text-white transition">
                                        Run
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($migrations)): ?>
            <div class="text-center py-8 text-zinc-400 text-sm">No migrations found.</div>
        <?php endif; ?>
    </div>

</div>
