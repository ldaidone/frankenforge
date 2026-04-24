<?php

declare(strict_types=1);

/**
 * Invoices table component.
 *
 * Expected variables:
 *   - $invoices: array of FrankenForge\Domains\Dashboard\Entities\Invoice
 */

$invoices ??= [];
?>

<div class="rounded-xl overflow-hidden" style="border: 1px solid var(--app-section-border)">
    <table class="w-full text-xs sm:text-sm">
        <thead style="background: var(--app-section)">
            <tr>
                <th class="px-4 py-3 text-left font-semibold" style="color: var(--app-text-muted-on-section)">Customer</th>
                <th class="px-4 py-3 text-right font-semibold" style="color: var(--app-text-muted-on-section)">Amount</th>
                <th class="px-4 py-3 text-left font-semibold" style="color: var(--app-text-muted-on-section)">Date</th>
                <th class="px-4 py-3 text-left font-semibold" style="color: var(--app-text-muted-on-section)">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $i => $invoice): ?>
            <tr class="border-t" style="border-color: var(--app-section-border); background: <?= $i % 2 === 0 ? 'transparent' : 'var(--app-section-hover)' ?>">
                <td class="px-4 py-3" style="color: var(--app-text-on-section)"><?= htmlspecialchars($invoice->customerName) ?></td>
                <td class="px-4 py-3 text-right font-mono" style="color: var(--app-text-on-section)"><?= htmlspecialchars($invoice->formattedAmount()) ?></td>
                <td class="px-4 py-3" style="color: var(--app-text-on-section)"><?= htmlspecialchars($invoice->formattedDate()) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-medium <?= htmlspecialchars($invoice->statusBadge()) ?>">
                        <?= htmlspecialchars($invoice->status->value) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($invoices)): ?>
            <tr class="border-t" style="border-color: var(--app-section-border)">
                <td colspan="4" class="px-4 py-6 text-center" style="color: var(--app-text-muted-on-section)">No invoices found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>