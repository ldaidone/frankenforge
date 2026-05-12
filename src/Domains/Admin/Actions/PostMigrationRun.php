<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Admin\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Session\FlashMessages;
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Runs or rolls back a specific migration.
 */
final class PostMigrationRun
{
    public function __construct(
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $user = $this->auth->user();
        if ($user === null || $user->mustChangePassword) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        $action = $request->body('action') ?? 'up';
        $target = $request->body('migration') ?? '';
        $migrationDir = __DIR__ . '/../../../../migrations';
        $db = $this->db();

        try {
            if ($action === 'down') {
                if ($target === '') {
                    // Rollback last
                    $applied = $db->fetchAll("SELECT migration FROM schema_migrations ORDER BY applied_at DESC LIMIT 1");
                    if ($applied === []) {
                        $this->flash->set('migrate_error', 'No migrations to rollback.');
                    } else {
                        $target = $applied[0]['migration'];
                    }
                }

                if ($target !== '') {
                    $appliedAll = $db->fetchAll("SELECT migration FROM schema_migrations ORDER BY applied_at DESC");
                    $appliedNames = array_map(fn($r) => $r['migration'], $appliedAll);
                    $targetIdx = array_search($target, $appliedNames, true);

                    // Cascade: rollback target and all newer migrations
                    if ($targetIdx !== false) {
                        $toRollback = array_slice($appliedNames, 0, $targetIdx + 1);
                        $rolled = 0;
                        foreach ($toRollback as $filename) {
                            $file = "{$migrationDir}/{$filename}";
                            if (file_exists($file)) {
                                $migration = require $file;
                                $migration($db, true);
                                $db->execute("DELETE FROM schema_migrations WHERE migration = ?", [$filename]);
                                $rolled++;
                            }
                        }
                        $this->flash->set('migrate_success', "Rolled back {$rolled} migration(s).");
                    } else {
                        $this->flash->set('migrate_error', 'Migration not found or already rolled back.');
                    }
                }
            } else {
                if ($target === '') {
                    // Run all pending
                    $files = glob("{$migrationDir}/*.php") ?: [];
                    sort($files);
                    $applied = $db->fetchAll("SELECT migration FROM schema_migrations");
                    $appliedNames = array_map(fn($r) => $r['migration'], $applied);
                    $ran = 0;

                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (in_array($filename, $appliedNames, true)) {
                            continue;
                        }
                        $migration = require $file;
                        $migration($db);
                        $db->execute("INSERT INTO schema_migrations (migration, applied_at) VALUES (?, datetime('now'))", [$filename]);
                        $ran++;
                    }

                    $this->flash->set('migrate_success', $ran > 0 ? "Ran {$ran} migration(s)." : 'No pending migrations.');
                } else {
                    // Run specific
                    $file = "{$migrationDir}/{$target}";
                    if (file_exists($file)) {
                        $applied = $db->fetchAll("SELECT migration FROM schema_migrations WHERE migration = ?", [$target]);
                        if ($applied === []) {
                            $migration = require $file;
                            $migration($db);
                            $db->execute("INSERT INTO schema_migrations (migration, applied_at) VALUES (?, datetime('now'))", [$target]);
                            $this->flash->set('migrate_success', "Ran {$target}.");
                        } else {
                            $this->flash->set('migrate_error', 'Migration already applied.');
                        }
                    } else {
                        $this->flash->set('migrate_error', 'Migration file not found.');
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->flash->set('migrate_error', $e->getMessage());
        }

        return $response->withStatus(302)->withHeader('Location', '/dashboard/migrations');
    }

    private function db(): \FrankenForge\Shared\Infrastructure\Database\Connection
    {
        $dsn = $_ENV['DATABASE_URL'] ?? 'sqlite:' . __DIR__ . '/../../../../storage/app.db';
        return new \FrankenForge\Shared\Infrastructure\Database\Connection($dsn);
    }
}
