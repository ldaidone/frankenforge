<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Overview</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Welcome back, <?= htmlspecialchars($user->name) ?></p>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $message): ?>
            <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-<?= $type === 'success' ? 'green' : ($type === 'error' ? 'red' : 'yellow') ?>-500/10 border border-<?= $type === 'success' ? 'green' : ($type === 'error' ? 'red' : 'yellow') ?>-500/30 text-<?= $type === 'success' ? 'green' : ($type === 'error' ? 'red' : 'yellow') ?>-400">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- System Info Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="rounded-xl p-4" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">PHP Version</div>
            <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100"><?= htmlspecialchars($info['php_version']) ?></div>
        </div>
        <div class="rounded-xl p-4" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Runtime</div>
            <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100"><?= $info['frankenphp'] === 'yes' ? 'FrankenPHP ✓' : htmlspecialchars($info['sapi']) ?></div>
        </div>
        <div class="rounded-xl p-4" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Memory Usage</div>
            <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100"><?= htmlspecialchars($info['memory']) ?></div>
        </div>
        <div class="rounded-xl p-4" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">OPcache</div>
            <div class="text-lg font-bold <?= $info['opcache'] === 'enabled' ? 'text-green-400' : 'text-yellow-400' ?>"><?= htmlspecialchars($info['opcache']) ?></div>
        </div>
        <div class="rounded-xl p-4" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Loaded Extensions</div>
            <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100"><?= count($info['extensions']) ?></div>
        </div>
        <div class="rounded-xl p-4" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">php.ini</div>
            <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate"><?= htmlspecialchars($info['ini_file']) ?></div>
        </div>
    </div>

    <!-- Extensions List -->
    <div class="rounded-xl p-6" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Loaded Extensions</h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($info['extensions'] as $ext): ?>
                <span class="px-2.5 py-1 rounded-md text-xs font-mono bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                    <?= htmlspecialchars($ext) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

</div>
