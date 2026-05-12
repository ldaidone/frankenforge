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

use FrankenForge\Domains\Dashboard\Entities\User;

interface UserRepositoryInterface
{
    public function findAll(): array;

    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function save(User $user): void;

    public function delete(string $id): void;
}
