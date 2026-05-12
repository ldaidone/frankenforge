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
use FrankenForge\Core\Logging\FileLogger;
use FrankenForge\Core\Session\FlashMessages;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Admin\Services\Auth;
use FrankenForge\Shared\Infrastructure\Database\Connection;

/**
 * Lists all database tables with row counts.
 */
final class GetTableViewer
{
    private const string VIEW = __DIR__ . '/../Views/database.html.php';
    private  $logger;

    public function __construct(
        private readonly Responder $responder,
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
        private readonly Connection $db,
    ) {
        $this->logger = new FileLogger(__DIR__ . '/../../../../storage/app.log');
    }

    public function __invoke(Request $request, Response $response, array $params = []): Response
    {
        $user = $this->auth->user();
        if ($user === null || $user->mustChangePassword) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        $tables = $this->db->fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        $rows = [];

        foreach ($tables as $table) {
            $name = $table['name'];
            $count = (int) $this->db->fetchOne("SELECT COUNT(*) as cnt FROM \"{$name}\"")['cnt'];
            $cols = $this->db->fetchAll("PRAGMA table_info(\"{$name}\")");
            $rows[] = [
                'name' => $name,
                'row_count' => $count,
                'columns' => count($cols),
                'column_names' => array_map(fn($c) => $c['name'], $cols),
            ];
        }

        $this->logger->info('Fetched Rows', ['tables' => var_export($rows, true)]);

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/dashboard-layout.html.php',
            data: [
                'title' => 'FrankenForge • Database',
                'user' => $user,
                'navItems' => $this->navItems('database'),
                'tables' => $rows,
                'flash' => $this->flash->all(),
            ],
        );
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
            ['label' => 'Database', 'href' => '/dashboard/database', 'icon' => 'fa-database', 'active' => $active === 'database'],
            ['label' => 'Migrations', 'href' => '/dashboard/migrations', 'icon' => 'fa-boxes-stacked', 'active' => false],
            ['label' => 'Logs', 'href' => '/dashboard/logs', 'icon' => 'fa-file-lines', 'active' => false],
            ['label' => 'Logout', 'href' => '/dashboard/logout', 'icon' => 'fa-right-from-bracket', 'active' =>false],
        ];
    }
}
