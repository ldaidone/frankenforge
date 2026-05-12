<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Dashboard\ValueObjects;

/**
 * Immutable Percentage value object.
 *
 * Stored as basis points (1 bp = 0.01%) to avoid floating-point issues.
 * Range: 0–10000 bp (0%–100%).
 */
final readonly class Percentage
{
    private const int MAX_BP = 10000;

    public function __construct(
        public int $basisPoints,
    ) {
        if ($basisPoints < 0 || $basisPoints > self::MAX_BP) {
            throw new \InvalidArgumentException(
                "Basis points must be 0–" . self::MAX_BP . ", got {$basisPoints}"
            );
        }
    }

    public static function fromPercent(float|int $percent): self
    {
        return new self((int) round($percent * 100));
    }

    public function percent(): float
    {
        return $this->basisPoints / 100;
    }

    public function formatted(): string
    {
        return number_format($this->percent(), 2) . '%';
    }

    public function of(Money $money): Money
    {
        $cents = (int) round($money->cents * $this->basisPoints / self::MAX_BP);
        return new Money($cents, $money->currency);
    }

    public function change(int $newBp): int
    {
        return $newBp - $this->basisPoints;
    }
}
