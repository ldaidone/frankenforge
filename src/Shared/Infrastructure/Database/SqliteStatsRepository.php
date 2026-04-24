<?php

declare(strict_types=1);

namespace FrankenForge\Shared\Infrastructure\Database;

use FrankenForge\Domains\Dashboard\Entities\Stat;
use FrankenForge\Domains\Dashboard\Repositories\StatsRepositoryInterface;

final class SqliteStatsRepository implements StatsRepositoryInterface
{
    private const TABLE = 'stats';

    public function __construct(
        private readonly Connection $db,
    ) {}

    public function findAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY label");

        return array_map(fn(array $row) => $this->toEntity($row), $rows);
    }

    public function findByKey(string $key): ?Stat
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE id = :id",
            ['id' => $key],
        );

        if ($row === null) {
            return null;
        }

        return $this->toEntity($row);
    }

    public function save(Stat $stat): void
    {
        $existing = $this->findByKey($stat->key);

        if ($existing === null) {
            $this->db->insert(self::TABLE, $this->toRow($stat));
        } else {
            $this->db->update(
                self::TABLE,
                $this->toRow($stat),
                'id = :id',
                ['id' => $stat->key],
            );
        }
    }

    public function delete(string $key): void
    {
        $this->db->delete(self::TABLE, 'id = :id', ['id' => $key]);
    }

    private function toEntity(array $row): Stat
    {
        return new Stat(
            key: $row['id'],
            label: $row['label'],
            value: $row['value'],
            icon: $row['icon'],
            trend: $row['trend'],
            up: isset($row['up']) ? (bool) $row['up'] : null,
        );
    }

    private function toRow(Stat $stat): array
    {
        return [
            'id' => $stat->key,
            'label' => $stat->label,
            'value' => $stat->value,
            'icon' => $stat->icon,
            'trend' => $stat->trend,
            'up' => $stat->up !== null ? (int) $stat->up : null,
            'updated_at' => date('c'),
        ];
    }
}