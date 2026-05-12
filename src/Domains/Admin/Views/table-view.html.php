<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <!-- Page Header -->
    <div class="mb-6">
        <a href="/dashboard/database" class="text-orange-500 hover:text-orange-400 text-sm underline font-medium">← Back to Database</a>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-3"><?= htmlspecialchars($table) ?></h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400"><?= $total ?> rows · page <?= $page ?> of <?= $lastPage ?></p>
    </div>

    <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $message): ?>
            <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-<?= $type === 'success' ? 'green' : 'red' ?>-500/10 border border-<?= $type === 'success' ? 'green' : 'red' ?>-500/30 text-<?= $type === 'success' ? 'green' : 'red' ?>-400">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Table -->
    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-left text-xs text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <?php foreach ($columnNames as $col): ?>
                        <?php
                        if ($col === $sortCol) {
                            $newDir = $sortDir === 'asc' ? 'desc' : 'asc';
                            $icon = $sortDir === 'asc' ? '↑' : '↓';
                        } else {
                            $newDir = 'asc';
                            $icon = '';
                        }
                        $sortUrl = "/dashboard/database/" . urlencode($table) . "?sort=" . urlencode($col) . "&dir={$newDir}&page=1";
                        ?>
                        <th class="px-4 py-3 font-medium">
                            <a href="<?= htmlspecialchars($sortUrl) ?>"
                               class="hover:text-orange-400 inline-flex items-center gap-1 transition-colors">
                                <?= htmlspecialchars($col) ?>
                                <?php if ($col === $sortCol): ?>
                                    <span class="text-orange-400"><?= $icon ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <?php foreach ($columnNames as $col): ?>
                                <td class="px-4 py-3 font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                    <?= $row[$col] === null ? '<span class="text-zinc-400 italic">NULL</span>' : htmlspecialchars((string) $row[$col]) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($rows)): ?>
            <div class="text-center py-8 text-zinc-400 text-sm">No rows.</div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($lastPage > 1): ?>
        <div class="flex items-center justify-between">
            <span class="text-xs text-zinc-500">
                Showing <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> of <?= $total ?>
            </span>
            <div class="flex gap-1">
                <?php
                $q = '';
                if ($sortCol !== '') {
                    $q = '&sort=' . urlencode($sortCol) . '&dir=' . urlencode($sortDir);
                }
                ?>
                <?php if ($page > 1): ?>
                    <a href="/dashboard/database/<?= urlencode($table) ?>?page=<?= $page - 1 ?><?= $q ?>"
                       class="px-3 py-1.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        ← Prev
                    </a>
                <?php endif; ?>
                <?php if ($page < $lastPage): ?>
                    <a href="/dashboard/database/<?= urlencode($table) ?>?page=<?= $page + 1 ?><?= $q ?>"
                       class="px-3 py-1.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
