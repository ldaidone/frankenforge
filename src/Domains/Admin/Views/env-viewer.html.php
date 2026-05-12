<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 py-6">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Environment</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">.env configuration viewer</p>
    </div>

    <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $message): ?>
            <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-<?= $type === 'success' ? 'green' : 'red' ?>-500/10 border border-<?= $type === 'success' ? 'green' : 'red' ?>-500/30 text-<?= $type === 'success' ? 'green' : 'red' ?>-400">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="/dashboard/env/save" class="mb-6">
        <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between px-4 py-2 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <code class="text-xs font-mono text-zinc-500"><?= htmlspecialchars($path) ?></code>
                <button type="submit" class="px-3 py-1 bg-orange-600 hover:bg-orange-500 text-white rounded text-xs font-semibold transition">
                    Save Changes
                </button>
            </div>
            <textarea name="env_content" rows="20"
                      class="w-full px-4 py-3 font-mono text-xs bg-zinc-900 text-green-400 focus:outline-none resize-none"><?= htmlspecialchars($raw) ?></textarea>
        </div>
    </form>

    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <div class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
            <span class="text-xs font-semibold text-zinc-500">Parsed Variables (<?= count(array_filter($entries, fn($e) => $e['key'] !== null)) ?>)</span>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs text-zinc-500">
                    <th class="px-4 py-2 font-medium">Key</th>
                    <th class="px-4 py-2 font-medium">Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <?php if ($entry['key'] !== null): ?>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="px-4 py-2 font-mono text-xs text-orange-500"><?= htmlspecialchars($entry['key']) ?></td>
                            <td class="px-4 py-2 font-mono text-xs text-zinc-400"><?= htmlspecialchars($entry['value']) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
