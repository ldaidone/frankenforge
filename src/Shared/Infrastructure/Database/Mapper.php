<?php

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
     * @template T of object
     * @param array<string, mixed> $row
     * @param class-string<T> $class
     * @return T
     */
    private static function toEntity(array $row, string $class): object
    {
        return new $class(...$row);
    }

    /**
     * Hydrate a list of entities from DB rows.
     *
     * @template T of object
     * @param list<array<string, mixed>> $rows
     * @param class-string<T> $class
     * @return list<T>
     */
    private static function toEntities(array $rows, string $class): array
    {
        return array_map(fn($row) => new $class(...$row), $rows);
    }
}
