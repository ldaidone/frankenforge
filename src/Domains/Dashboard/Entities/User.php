<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Entities;

/**
 * Immutable User entity.
 *
 * Represents a registered user in the dashboard context.
 */
final readonly class User
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $role,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $lastLoginAt = null,
    ) {}

    public function isActive(): bool
    {
        return $this->lastLoginAt !== null
            && $this->lastLoginAt > new \DateTimeImmutable('-30 days');
    }

    public function initials(): string
    {
        $parts = explode(' ', trim($this->name));
        $first = $parts[0][0] ?? '';
        $last = $parts[count($parts) - 1][0] ?? '';
        return strtoupper($first . $last);
    }
}
