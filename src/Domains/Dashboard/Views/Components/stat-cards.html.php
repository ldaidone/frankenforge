<?php

declare(strict_types=1);

/**
 * Stat cards grid component.
 *
 * Expected variables:
 *   - $stats: array of FrankenForge\Domains\Dashboard\Entities\Stat
 */

$stats ??= [];
?>

<div id="stat-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php foreach ($stats as $stat): ?>
        <div class="rounded-xl p-5" style="background: var(--app-section); border: 1px solid var(--app-section-border); color: var(--app-text-on-section)">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-wider" style="color: var(--app-text-muted-on-section)"><?= htmlspecialchars($stat->label) ?></span>
                <i class="fa-solid <?= htmlspecialchars($stat->icon) ?>" style="color: #fb923c99"></i>
            </div>
            <div class="text-2xl font-bold tabular-nums text-white"><?= htmlspecialchars($stat->value) ?></div>
            <?php if ($stat->trend !== ''): ?>
                <div class="mt-1 text-xs <?= htmlspecialchars($stat->trendColor()) ?>">
                    <?php if ($stat->trendIcon() !== ''): ?>
                        <i class="fa-solid <?= htmlspecialchars($stat->trendIcon()) ?>"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($stat->trend) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
