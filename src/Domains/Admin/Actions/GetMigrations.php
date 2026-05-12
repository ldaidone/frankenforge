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
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Shows migration status and allows running migrations.
 */
final class GetMigrations
{
    private const string VIEW = __DIR__ . '/../Views/migrations.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response, array $params = []): Response
    {
        $user = $this->auth->user();
        if ($user === null || $user->mustChangePassword) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        $migrationDir = __DIR__ . '/../../../../migrations';
        $files = glob("{$migrationDir}/*.php") ?: [];
        sort($files);

        $applied = $this->getAppliedMigrations();
        $all = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $timestamp = preg_replace('/^(\d{14})_.*/', '$1', $filename);
            $appliedAt = null;
            foreach ($applied as $row) {
                if ($row['migration'] === $filename) {
                    $appliedAt = $row['applied_at'];
                    break;
                }
            }
            $all[] = [
                'file' => $filename,
                'timestamp' => $timestamp,
                'applied' => $appliedAt !== null,
                'applied_at' => $appliedAt,
            ];
        }

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/dashboard-layout.html.php',
            data: [
                'title' => 'FrankenForge • Migrations',
                'user' => $user,
                'navItems' => $this->navItems('migrations'),
                'migrations' => $all,
                'pending' => count(array_filter($all, fn($m) => !$m['applied'])),
                'flash' => $this->flash->all(),
            ],
        );
    }

    /**
     * @return list<array{migration: string, applied_at: string}>
     */
    private function getAppliedMigrations(): array
    {
        try {
            return $this->db()->fetchAll("SELECT migration, applied_at FROM schema_migrations ORDER BY applied_at");
        } catch (\Throwable) {
            return [];
        }
    }

    private function db(): \FrankenForge\Shared\Infrastructure\Database\Connection
    {
        $dsn = $_ENV['DATABASE_URL'] ?? 'sqlite:' . __DIR__ . '/../../../../storage/app.db';
        return new \FrankenForge\Shared\Infrastructure\Database\Connection($dsn);
    }

    /**
     * @return list<array{label: string, href: string, icon: string, active: bool, divider?: bool}>
     */
    private function navItems(string $active): array
    {
        return [
            ['label' => 'Overview', 'href' => '/dashboard/overview', 'icon' => 'fa-gauge-high', 'active' => false],
            ['label' => 'Profile', 'href' => '/dashboard/profile', 'icon' => 'fa-user', 'active' => false],
            ['label' => 'Environment', 'href' => '/dashboard/env', 'icon' => 'fa-key', 'active' => false],
            ['label' => 'Database', 'href' => '/dashboard/database', 'icon' => 'fa-database', 'active' => false],
            ['label' => 'Migrations', 'href' => '/dashboard/migrations', 'icon' => 'fa-boxes-stacked', 'active' => $active === 'migrations'],
            ['label' => 'Logs', 'href' => '/dashboard/logs', 'icon' => 'fa-file-lines', 'active' => false],
            ['label' => 'Logout', 'href' => '/dashboard/logout', 'icon' => 'fa-right-from-bracket', 'active' =>false],
        ];
    }
}
