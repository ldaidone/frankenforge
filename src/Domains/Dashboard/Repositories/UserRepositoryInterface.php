<?php

declare(strict_types=1);

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