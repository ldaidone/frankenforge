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
 * Migration CLI — runs outside the web worker.
 *
 * Usage:
 *   php bin/migrate.php up          Apply all pending migrations
 *   php bin/migrate.php down [N]    Roll back last N migrations (default 1)
 *   php bin/migrate.php status      Show migration status
 */

require __DIR__ . '/../vendor/autoload.php';

use FrankenForge\Shared\Infrastructure\Database\Connection;
use FrankenForge\Shared\Infrastructure\Database\Migrator;

$command = $argv[1] ?? 'status';
$steps = (int) ($argv[2] ?? 1);

$dsn = $_ENV['DATABASE_URL']
    ?? $_SERVER['DATABASE_URL']
    ?? 'sqlite:' . __DIR__ . '/../storage/app.db';

// Ensure storage directory exists
$storageDir = dirname($dsn) === 'sqlite:' ? __DIR__ . '/../storage' : dirname(substr($dsn, 7));
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$db = new Connection($dsn);
$migrator = new Migrator($db, __DIR__ . '/../migrations');

match ($command) {
    'up' => $migrator->up(),
    'down' => $migrator->down($steps),
    'status' => printStatus($migrator),
    default => die("Unknown command: {$command}. Use: up, down [N], status\n"),
};

/**
 * @param Migrator $migrator
 */
function printStatus(Migrator $migrator): void
{
    $status = $migrator->status();
    if (empty($status)) {
        echo "No migrations found.\n";
        return;
    }
    foreach ($status as $file => $state) {
        $icon = $state === 'applied' ? '✓' : '○';
        $color = $state === 'applied' ? "\033[32m" : "\033[33m";
        $reset = "\033[0m";
        echo "  {$color}{$icon}{$reset} {$file} ({$state})\n";
    }
}
