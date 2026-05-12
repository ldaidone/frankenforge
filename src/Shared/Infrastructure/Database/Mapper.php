<?php
/**
 * FrankenForge — FrankenForge\Shared\Infrastructure\Database
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Shared\Infrastructure\Database;

/**
 * Data Mapper helpers for hydrating pure domain entities from DB rows.
 *
 * Usage in a repository:
 *   $user = UserMapper::toEntity($row);
 *   $users = UserMapper::toEntities($rows);
 */
trait Mapper
{
    /**
     * Hydrate a single entity from a DB row.
     *
     * @param array $row
     * @param string $class
     * @return mixed
     */
    private static function toEntity(array $row, string $class): object
    {
        return new $class(...$row);
    }

    /**
     * Hydrate a list of entities from DB rows.
     *
     * @param array $rows
     * @param string $class
     * @return array
     */
    private static function toEntities(array $rows, string $class): array
    {
        return array_map(fn($row) => new $class(...$row), $rows);
    }
}
