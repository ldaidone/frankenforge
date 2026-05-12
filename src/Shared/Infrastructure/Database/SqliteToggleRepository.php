<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Shared\Infrastructure\Database;

use FrankenForge\Domains\Dashboard\Repositories\ToggleRepositoryInterface;
use FrankenForge\Shared\Infrastructure\Database\Connection;

final class SqliteToggleRepository implements ToggleRepositoryInterface
{
    private const string TABLE = 'toggles';

    public function __construct(
        private readonly Connection $db,
    ) {}

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY label");
    }

    public function findById(string $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE id = :id",
            ['id' => $id],
        );
    }

    public function toggle(string $id): array
    {
        $current = $this->findById($id);

        if ($current === null) {
            return ['success' => false, 'error' => 'Toggle not found'];
        }

        $newValue = (int) !$current['enabled'];

        $this->db->update(
            self::TABLE,
            ['enabled' => $newValue],
            'id = :id',
            ['id' => $id],
        );

        return [
            'success' => true,
            'id' => $id,
            'enabled' => (bool) $newValue,
        ];
    }
}
