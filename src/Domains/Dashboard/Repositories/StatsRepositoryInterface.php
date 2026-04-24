<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Repositories;

use FrankenForge\Domains\Dashboard\Entities\Stat;

interface StatsRepositoryInterface
{
    public function findAll(): array;

    public function findByKey(string $key): ?Stat;

    public function save(Stat $stat): void;

    public function delete(string $key): void;
}