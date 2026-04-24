<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Repositories;

interface ToggleRepositoryInterface
{
    public function findAll(): array;

    public function findById(string $id): ?array;

    public function toggle(string $id): array;
}