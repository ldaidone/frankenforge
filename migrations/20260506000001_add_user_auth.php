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
 * 002_add_user_auth — Add password_hash and must_change_password to users table.
 *
 * @param \FrankenForge\Shared\Infrastructure\Database\Connection $db
 * @param bool $down
 */
return function (\FrankenForge\Shared\Infrastructure\Database\Connection $db, bool $down = false): void {
    if ($down) {
        $db->execute('ALTER TABLE users DROP COLUMN password_hash');
        $db->execute('ALTER TABLE users DROP COLUMN must_change_password');
        return;
    }

    $db->execute('ALTER TABLE users ADD COLUMN password_hash TEXT NOT NULL DEFAULT ""');
    $db->execute('ALTER TABLE users ADD COLUMN must_change_password INTEGER NOT NULL DEFAULT 0');
};
