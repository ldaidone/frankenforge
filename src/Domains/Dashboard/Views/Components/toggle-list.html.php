<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
/**
 * Toggle switch list component.
 *
 * Renders a list of feature toggles that can be flipped via HTMX POST.
 * Note: HTMX swaps this content into #toggle-list, so no outer wrapper is needed.
 *
 * Expected variables:
 *   - $toggles: array of ['id' => string, 'label' => string, 'enabled' => bool]
 *   - $toggleUrl: string (POST target, defaults to /dashboard/toggle/{id}/toggle)
 */

$toggles ??= [];
$toggleUrl ??= '/dashboard/toggle/{id}';
?>

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
