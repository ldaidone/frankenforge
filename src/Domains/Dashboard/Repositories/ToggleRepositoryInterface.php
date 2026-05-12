<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Dashboard\Repositories;

interface ToggleRepositoryInterface
{
    public function findAll(): array;

    public function findById(string $id): ?array;

    public function toggle(string $id): array;
}
