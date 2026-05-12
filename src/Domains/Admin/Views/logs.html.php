<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Logs</h1>
    </div>

    <!-- Level Filter -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="/dashboard/logs?level=all&page=1"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?= $level === 'all' ? 'bg-orange-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' ?>">
            All (<?= $counts['all'] ?>)
        </a>
        <?php foreach ($levels as $l): ?>
            <?php $color = match ($l) { 'error' => 'red', 'warning' => 'yellow', 'info' => 'blue', default => 'zinc' }; ?>
            <a href="/dashboard/logs?level=<?= $l ?>&page=1"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?= $level === $l ? "bg-{$color}-600 text-white" : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700" ?>">
                <?= ucfirst($l) ?> (<?= $counts[$l] ?? 0 ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Log Lines -->
    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <?php if (empty($lines)): ?>
            <div class="text-center py-12 text-zinc-400">
                <i class="fa-solid fa-file-lines text-4xl mb-4"></i>
                <p>No log entries.</p>
            </div>
        <?php else: ?>
            <?php foreach ($lines as $line): ?>
                <?php $color = match ($line['level'] ?? '') { 'error' => 'border-red-500/30 bg-red-500/5 text-red-400', 'warning' => 'border-yellow-500/30 bg-yellow-500/5 text-yellow-400', 'info' => 'border-blue-500/30 bg-blue-500/5 text-blue-400', default => 'border-zinc-700 bg-transparent text-zinc-400' }; ?>
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-start gap-3 <?= $color ?>">
                    <span class="font-mono text-xs mt-0.5 opacity-60"><?= $line['level'] ?? '?' ?></span>
                    <div class="flex-1">
                        <div class="font-mono text-xs"><?= htmlspecialchars($line['message'] ?? '') ?></div>
                        <?php if (!empty($line['context']) && is_array($line['context'])): ?>
                            <pre class="mt-1 text-xs opacity-60 overflow-x-auto"><?= htmlspecialchars(json_encode($line['context'], JSON_PRETTY_PRINT)) ?></pre>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs opacity-40 whitespace-nowrap"><?= $line['time'] ?? '' ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($lastPage ?? 1) > 1): ?>
        <div class="flex items-center justify-between mt-4">
            <span class="text-xs text-zinc-500">
                Showing <?= (($page ?? 1) - 1) * $perPage + 1 ?>–<?= min(($page ?? 1) * $perPage, $total) ?> of <?= $total ?>
            </span>
            <div class="flex gap-1">
                <?php
                $lvl = $level !== 'all' ? '&level=' . urlencode($level) : '';
                ?>
                <?php if (($page ?? 1) > 1): ?>
                    <a href="/dashboard/logs?page=<?= ($page ?? 1) - 1 ?><?= $lvl ?>"
                       class="px-3 py-1.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        ← Prev
                    </a>
                <?php endif; ?>
                <?php if (($page ?? 1) < $lastPage): ?>
                    <a href="/dashboard/logs?page=<?= ($page ?? 1) + 1 ?><?= $lvl ?>"
                       class="px-3 py-1.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
