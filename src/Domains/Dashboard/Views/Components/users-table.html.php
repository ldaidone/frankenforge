<?php

declare(strict_types=1);

/**
 * Users table component.
 *
 * Expected variables:
 *   - $users: array of FrankenForge\Domains\Dashboard\Entities\User
 */

$users ??= [];
?>

<div class="rounded-xl overflow-hidden" style="border: 1px solid var(--app-section-border)">
    <table class="w-full text-xs sm:text-sm">
        <thead style="background: var(--app-section)">
            <tr>
                <th class="px-4 py-3 text-left font-semibold" style="color: var(--app-text-muted-on-section)">Name</th>
                <th class="px-4 py-3 text-left font-semibold" style="color: var(--app-text-muted-on-section)">Email</th>
                <th class="px-4 py-3 text-left font-semibold" style="color: var(--app-text-muted-on-section)">Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $i => $user): ?>
            <tr class="border-t" style="border-color: var(--app-section-border); background: <?= $i % 2 === 0 ? 'transparent' : 'var(--app-section-hover)' ?>">
                <td class="px-4 py-3" style="color: var(--app-text-on-section)">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" style="background: var(--app-toggle-on); color: white">
                            <?= htmlspecialchars(substr($user->name, 0, 1)) ?>
                        </div>
                        <?= htmlspecialchars($user->name) ?>
                    </div>
                </td>
                <td class="px-4 py-3" style="color: var(--app-text-on-section)"><?= htmlspecialchars($user->email) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-medium" style="background: var(--app-toggle-on); color: white">
                        <?= htmlspecialchars($user->role) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr class="border-t" style="border-color: var(--app-section-border)">
                <td colspan="3" class="px-4 py-6 text-center" style="color: var(--app-text-muted-on-section)">No users found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>