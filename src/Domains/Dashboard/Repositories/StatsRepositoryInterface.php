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

use FrankenForge\Domains\Dashboard\Entities\Stat;

interface StatsRepositoryInterface
{
    public function findAll(): array;

    public function findByKey(string $key): ?Stat;

    public function save(Stat $stat): void;

    public function delete(string $key): void;
}
