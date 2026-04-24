<?php

declare(strict_types=1);

/**
 * Toggle switch list component.
 *
 * Renders a list of feature toggles that can be flipped via HTMX POST.
 *
 * Expected variables:
 *   - $toggles: array of ['id' => string, 'label' => string, 'enabled' => bool]
 *   - $toggleUrl: string (POST target, defaults to /api/toggles/{id}/toggle)
 */

$toggles ??= [];
$toggleUrl ??= null;
?>

<div id="toggle-list" class="space-y-3">
    <?php foreach ($toggles as $t): ?>
        <div class="flex items-center justify-between py-2">
            <span class="text-sm" style="color: var(--app-text-on-section)"><?= htmlspecialchars($t['label']) ?></span>

            <button hx-post="/dashboard/toggle/<?= htmlspecialchars($t['id']) ?>"
                    hx-target="#toggle-list"
                    hx-swap="innerHTML swap:200ms"
                    class="relative w-10 h-5 rounded-full transition-colors duration-200"
                    style="background: <?= $t['enabled'] ? 'var(--app-toggle-on, #22c55e)' : 'var(--app-toggle-off, #374151)' ?>">
                <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200
                             <?= $t['enabled'] ? 'translate-x-5' : 'translate-x-0' ?>">
                </span>
            </button>
        </div>
    <?php endforeach; ?>
</div>
