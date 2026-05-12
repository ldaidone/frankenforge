<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Admin\Repositories;

use FrankenForge\Domains\Admin\Entities\AdminUser;

interface AdminUserRepositoryInterface
{
    public function findAll(): array;

    public function findById(string $id): ?AdminUser;

    public function findByEmail(string $email): ?AdminUser;

    public function save(AdminUser $user): void;

    public function updatePassword(string $id, string $hash): void;

    public function clearMustChangePassword(string $id): void;

    public function verifyPassword(string $password, AdminUser $user): bool;
}
