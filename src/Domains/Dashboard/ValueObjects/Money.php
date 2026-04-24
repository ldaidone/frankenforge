<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\ValueObjects;

/**
 * Immutable Money value object.
 *
 * Stores amounts as integer cents to avoid floating-point errors.
 * Currency is an ISO-4217 3-letter code.
 */
final readonly class Money
{
    public function __construct(
        public int $cents,
        public string $currency = 'USD',
    ) {}

    public static function fromAmount(float|int $amount, string $currency = 'USD'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function amount(): float
    {
        return $this->cents / 100;
    }

    public function symbol(): string
    {
        return match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            default => $this->currency . ' ',
        };
    }

    public function formatted(): string
    {
        return $this->symbol() . number_format($this->amount(), 2);
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                "Cannot add {$other->currency} to {$this->currency}"
            );
        }
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function compareTo(self $other): int
    {
        return $this->cents <=> $other->cents;
    }
}
