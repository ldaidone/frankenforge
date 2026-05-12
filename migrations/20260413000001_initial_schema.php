<?php
/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */

declare(strict_types=1);

/**
 * 001_initial_schema — Base tables for the dashboard domain.
 *
 * Usage:
 *   $migration = require 'migrations/20260413000001_initial_schema.php';
 *   $migration($db);           // up
 *   $migration($db, true);     // down (rollback)
 *
 * @param \FrankenForge\Shared\Infrastructure\Database\Connection $db
 * @param bool $down
 */
return function (\FrankenForge\Shared\Infrastructure\Database\Connection $db, bool $down = false): void {
    if ($down) {
        $db->execute('DROP TABLE IF EXISTS toggles');
        $db->execute('DROP TABLE IF EXISTS invoices');
        $db->execute('DROP TABLE IF EXISTS stats');
        $db->execute('DROP TABLE IF EXISTS users');
        return;
    }

    $db->execute(<<<'SQL'
        CREATE TABLE IF NOT EXISTS users (
            id          TEXT PRIMARY KEY,
            name        TEXT NOT NULL,
            email       TEXT UNIQUE NOT NULL,
            role        TEXT NOT NULL DEFAULT 'user',
            created_at  TEXT NOT NULL DEFAULT (datetime('now')),
            last_login_at TEXT
        )
    SQL);

    $db->execute(<<<'SQL'
        CREATE TABLE IF NOT EXISTS stats (
            id          TEXT PRIMARY KEY,
            label       TEXT NOT NULL,
            value       TEXT NOT NULL,
            icon        TEXT NOT NULL DEFAULT '',
            trend       TEXT NOT NULL DEFAULT '',
            up          INTEGER,
            updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
        )
    SQL);

    $db->execute(<<<'SQL'
        CREATE TABLE IF NOT EXISTS invoices (
            id            TEXT PRIMARY KEY,
            customer_name TEXT NOT NULL,
            amount_cents  INTEGER NOT NULL,
            currency      TEXT NOT NULL DEFAULT 'USD',
            issued_at     TEXT NOT NULL,
            status        TEXT NOT NULL DEFAULT 'draft'
        )
    SQL);

    $db->execute(<<<'SQL'
        CREATE TABLE IF NOT EXISTS toggles (
            id      TEXT PRIMARY KEY,
            label   TEXT NOT NULL,
            enabled INTEGER NOT NULL DEFAULT 0
        )
    SQL);
};
