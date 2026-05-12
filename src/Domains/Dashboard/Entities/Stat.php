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
 * Immutable Stat metric entity.
 *
 * Represents a single dashboard stat card value with trend direction.
 */
final readonly class Stat
{
    public function __construct(
        public string $key,
        public string $label,
        public string $value,
        public string $icon,
        public string $trend,
        public ?bool $up = null,
    ) {}

    public function trendColor(): string
    {
        return match ($this->up) {
            true => 'text-green-400',
            false => 'text-red-400',
            null => 'text-zinc-500',
        };
    }

    public function trendIcon(): string
    {
        return match ($this->up) {
            true => 'fa-arrow-up',
            false => 'fa-arrow-down',
            null => '',
        };
    }
}
