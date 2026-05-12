<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Admin\Entities;

/**
 * Immutable AdminUser entity.
 *
 * Represents an authenticated user with password and login state.
 */
final readonly class AdminUser
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $passwordHash,
        public bool $mustChangePassword,
        public \DateTimeImmutable $createdAt,
    ) {}

    public function initials(): string
    {
        $parts = explode(' ', trim($this->name));
        $first = $parts[0][0] ?? '';
        $last = $parts[count($parts) - 1][0] ?? '';
        return strtoupper($first . $last);
    }
}
