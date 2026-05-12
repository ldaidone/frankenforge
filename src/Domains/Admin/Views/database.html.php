<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Database</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1"><?= count($tables) ?> tables</p>
    </div>

    <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $message): ?>
            <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-<?= $type === 'success' ? 'green' : 'red' ?>-500/10 border border-<?= $type === 'success' ? 'green' : 'red' ?>-500/30 text-<?= $type === 'success' ? 'green' : 'red' ?>-400">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($tables as $table): ?>
            <a href="/dashboard/database/<?= urlencode($table['name']) ?>"
               class="rounded-xl p-5 transition hover:ring-2 hover:ring-orange-500/50"
               style="background: var(--app-section); border: 1px solid var(--app-section-border)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100 font-mono"><?= htmlspecialchars($table['name']) ?></span>
                    <i class="fa-solid fa-table text-zinc-400"></i>
                </div>
                <div class="flex items-center gap-4 text-xs text-zinc-500">
                    <span><?= $table['row_count'] ?> rows</span>
                    <span>·</span>
                    <span><?= $table['columns'] ?> columns</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1">
                    <?php foreach (array_slice($table['column_names'], 0, 3) as $col): ?>
                        <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500 text-xs font-mono"><?= htmlspecialchars($col) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($table['column_names']) > 3): ?>
                        <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500 text-xs">+<?= count($table['column_names']) - 3 ?></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($tables)): ?>
        <div class="text-center py-12 text-zinc-400">
            <i class="fa-solid fa-database text-4xl mb-4"></i>
            <p>No tables found.</p>
        </div>
    <?php endif; ?>

</div>
