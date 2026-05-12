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

/**
 * PHP-based migration runner.
 *
 * Each migration file in migrations/ must be named:
 *   YYYYMMDDHHMMSS_description.php
 * and return a callable: fn(Connection $db): void
 *
 * A `schema_migrations` table tracks which migrations have been applied.
 */
final class Migrator
{
    private readonly Connection $db;
    private readonly string $migrationDir;
    private const string TABLE = 'schema_migrations';

    public function __construct(Connection $db, string $migrationDir)
    {
        $this->db = $db;
        $this->migrationDir = rtrim($migrationDir, '/');
    }

    /**
     * Run all pending migrations.
     *
     * @return list<string> applied migration filenames
     */
    public function up(): array
    {
        $this->ensureTable();
        $applied = $this->getApplied();
        $applied = [];
        foreach ($this->getPending() as $file) {
            $migration = require "{$this->migrationDir}/{$file}";
            $migration($this->db);
            $this->markApplied($file);
            $applied[] = $file;
            echo "  ✓ {$file}\n";
        }
        return $applied;
    }

    /**
     * Roll back the last N migrations.
     *
     * @param int $steps
     * @return list<string> rolled back filenames
     */
    public function down(int $steps = 1): array
    {
        $this->ensureTable();
        $applied = $this->getApplied();
        if (empty($applied)) {
            echo "  Nothing to roll back.\n";
            return [];
        }
        $toRollback = array_slice(array_reverse($applied), 0, $steps);
        $rolled = [];
        foreach ($toRollback as $file) {
            $migration = require "{$this->migrationDir}/{$file}";
            $migration($this->db, down: true);
            $this->markRolledBack($file);
            $rolled[] = $file;
            echo "  ↩ {$file}\n";
        }
        return $rolled;
    }

    /**
     * List migration status.
     *
     * @return array<string, string> [filename => 'applied'|'pending']
     */
    public function status(): array
    {
        $this->ensureTable();
        $applied = $this->getApplied();
        $status = [];
        foreach ($this->getFiles() as $file) {
            $status[$file] = in_array($file, $applied) ? 'applied' : 'pending';
        }
        return $status;
    }

    /**
     * @return list<string>
     */
    private function getPending(): array
    {
        $applied = $this->getApplied();
        return array_values(array_filter($this->getFiles(), fn($f) => !in_array($f, $applied)));
    }

    /**
     * @return list<string>
     */
    private function getFiles(): array
    {
        if (!is_dir($this->migrationDir)) {
            return [];
        }
        $files = scandir($this->migrationDir, SCANDIR_SORT_ASCENDING);
        return array_values(array_filter($files ?? [], fn($f) => preg_match('/^\d{14}_.*\.php$/', $f)));
    }

    /**
     * @return list<string>
     */
    private function getApplied(): array
    {
        $rows = $this->db->fetchAll("SELECT migration FROM " . self::TABLE . " ORDER BY migration ASC");
        return array_map(fn($r) => (string) $r['migration'], $rows);
    }

    private function markApplied(string $file): void
    {
        $this->db->execute("INSERT INTO " . self::TABLE . " (migration, applied_at) VALUES (?, ?)", [
            $file, date('Y-m-d H:i:s'),
        ]);
    }

    private function markRolledBack(string $file): void
    {
        $this->db->execute("DELETE FROM " . self::TABLE . " WHERE migration = ?", [$file]);
    }

    private function ensureTable(): void
    {
        $this->db->execute(
            "CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
                migration TEXT PRIMARY KEY,
                applied_at TEXT NOT NULL
            )"
        );
    }
}
