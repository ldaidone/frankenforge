<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Dashboard\Entities;

/**
 * Immutable Invoice entity.
 *
 * Represents a billable invoice record for the recent-activity table.
 */
final readonly class Invoice
{
    public function __construct(
        public string $id,
        public string $customerName,
        public int $amountCents,
        public string $currency,
        public \DateTimeImmutable $issuedAt,
        public InvoiceStatus $status,
    ) {}

    public function formattedAmount(): string
    {
        return match ($this->currency) {
            'USD' => '$' . number_format($this->amountCents / 100, 2),
            'EUR' => '€' . number_format($this->amountCents / 100, 2),
            default => number_format($this->amountCents / 100, 2) . ' ' . $this->currency,
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            InvoiceStatus::Paid => 'bg-green-500/10 text-green-400',
            InvoiceStatus::Pending => 'bg-yellow-500/10 text-yellow-400',
            InvoiceStatus::Overdue => 'bg-red-500/10 text-red-400',
            InvoiceStatus::Draft => 'bg-zinc-500/10 text-zinc-400',
        };
    }

    public function formattedDate(): string
    {
        return $this->issuedAt->format('M j, Y');
    }
}

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
}
