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
 * Recent activity / invoice table component.
 *
 * Expected variables:
 *   - $invoices: array of FrankenForge\Domains\Dashboard\Entities\Invoice
 */

use FrankenForge\Domains\Dashboard\Entities\Invoice;

$invoices ??= [];
?>

<div id="recent-activity" class="rounded-xl overflow-hidden" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b" style="border-color: var(--app-section-border)">
        <h2 class="text-xs sm:text-sm font-semibold uppercase tracking-wider" style="color: var(--app-text-on-section)">Recent Invoices</h2>
    </div>

    <?php if (empty($invoices)): ?>
        <div class="px-4 sm:px-6 py-8 sm:py-12 text-center text-xs sm:text-sm" style="color: var(--app-text-muted-on-section)">
            No invoices yet.
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm min-w-[500px]">
                <thead>
                    <tr class="border-b" style="border-color: var(--app-section-border); color: var(--app-text-muted-on-section)">
                        <th class="text-left px-3 sm:px-6 py-2 sm:py-3 font-medium whitespace-nowrap">Invoice</th>
                        <th class="text-left px-3 sm:px-6 py-2 sm:py-3 font-medium">Customer</th>
                        <th class="text-left px-3 sm:px-6 py-2 sm:py-3 font-medium whitespace-nowrap">Date</th>
                        <th class="text-right px-3 sm:px-6 py-2 sm:py-3 font-medium whitespace-nowrap">Amount</th>
                        <th class="text-center px-3 sm:px-6 py-2 sm:py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr class="border-b transition" style="border-color: color-mix(in srgb, var(--app-section-border) 50%, transparent)">
                            <td class="px-3 sm:px-6 py-2 sm:py-3 font-mono" style="color: var(--app-text-muted-on-section)">
                                #<?= htmlspecialchars($inv->id) ?>
                            </td>
                            <td class="px-3 sm:px-6 py-2 sm:py-3" style="color: var(--app-text-on-section)">
                                <?= htmlspecialchars($inv->customerName) ?>
                            </td>
                            <td class="px-3 sm:px-6 py-2 sm:py-3 whitespace-nowrap" style="color: var(--app-text-muted-on-section)">
                                <?= htmlspecialchars($inv->formattedDate()) ?>
                            </td>
                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-right tabular-nums font-medium whitespace-nowrap" style="color: var(--app-text-on-section)">
                                <?= htmlspecialchars($inv->formattedAmount()) ?>
                            </td>
                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-center">
                                <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-xs font-medium <?= $inv->statusBadge() ?>">
                                    <?= ucfirst($inv->status->value) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
