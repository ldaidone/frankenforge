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

use PDO;
use PDOStatement;

/**
 * Lightweight PDO wrapper for worker-mode reuse.
 *
 * In FrankenPHP worker mode this connection is created once
 * and reused across requests — no per-request bootstrap overhead.
 */
final class Connection
{
    private readonly PDO $pdo;

    /**
     * @param string $dsn   PDO DSN (e.g. "sqlite:/path/to/app.db")
     * @param string|null $user
     * @param string|null $pass
     */
    public function __construct(
        string $dsn,
        ?string $user = null,
        ?string $pass = null,
    ) {
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ]);

        // SQLite-specific pragmas
        if (str_starts_with($dsn, 'sqlite:')) {
            $this->pdo->exec('PRAGMA journal_mode=WAL');
            $this->pdo->exec('PRAGMA foreign_keys=ON');
            $this->pdo->exec('PRAGMA busy_timeout=5000');
        }
    }

    /**
     * Get the raw PDO instance (for advanced use).
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a query with bound parameters.
     *
     * @param string $sql
     * @param array<string|int, mixed> $params
     * @return PDOStatement
     */
    public function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row.
     *
     * @template T of object
     * @param string $sql
     * @param array<string|int, mixed> $params
     * @param class-string<T>|null $class
     * @return T|array<string,mixed>|null
     */
    public function fetchOne(string $sql, array $params = [], ?string $class = null): array|object|null
    {
        $stmt = $this->execute($sql, $params);
        if ($class !== null) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Fetch all rows.
     *
     * @template T of object
     * @param string $sql
     * @param array<string|int, mixed> $params
     * @param class-string<T>|null $class
     * @return list<T|mixed>
     */
    public function fetchAll(string $sql, array $params = [], ?string $class = null): array
    {
        $stmt = $this->execute($sql, $params);
        if ($class !== null) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }
        $rows = $stmt->fetchAll();
        return $rows === false ? [] : $rows;
    }

    /**
     * Insert a row and return the last insert ID.
     *
     * @param string $table
     * @param array<string, mixed> $data
     * @return string
     */
    public function insert(string $table, array $data): string
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_map(fn($c) => ":{$c}", $columns));
        $columnList = implode(', ', $columns);
        $sql = "INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})";
        $this->execute($sql, $data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update rows in a table.
     *
     * @param string $table
     * @param array<string, mixed> $data
     * @param string $where  e.g. "id = :id"
     * @param array<string, mixed> $whereParams
     * @return int affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        foreach ($data as $col => $_val) {
            $setParts[] = "{$col} = :_update_{$col}";
        }
        $setClause = implode(', ', $setParts);
        // Rename data keys to avoid collision with where params
        $prefixed = [];
        foreach ($data as $col => $val) {
            $prefixed["_update_{$col}"] = $val;
        }
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = $this->execute($sql, [...$prefixed, ...$whereParams]);
        return $stmt->rowCount();
    }

    /**
     * Delete rows from a table.
     *
     * @param string $table
     * @param string $where
     * @param array<string, mixed> $params
     * @return int affected rows
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Run a transaction block.
     *
     * @template T
     * @param callable(Connection): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
