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
 * Shows paginated rows for a specific database table.
 */
final class GetTableView
{
    private const string VIEW = __DIR__ . '/../Views/table-view.html.php';
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

        $this->logger->info('GetTableView invoked', ['params' => $params, 'query' => $request->query()]);

        $table = $params['table'] ?? '';
        $page = max(1, (int) ($request->query('page') ?? 1));
        $perPage = 20;
        $sortCol = $request->query('sort') ?? '';
        $sortDir = strtolower($request->query('dir') ?? 'asc');
        if (!in_array($sortDir, ['asc', 'desc'], true)) $sortDir = 'asc';

        $tables = $this->db->fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        $tableNames = array_map(fn($t) => $t['name'], $tables);

        if (!in_array($table, $tableNames, true)) {
            $this->flash->set('db_error', "Table '{$table}' not found.");
            return $response->withStatus(302)->withHeader('Location', '/dashboard/database');
        }

        $columns = $this->db->fetchAll("PRAGMA table_info(\"{$table}\")");
        $columnNames = array_map(fn($c) => $c['name'], $columns);

        if ($sortCol !== '' && in_array($sortCol, $columnNames, true)) {
            $orderClause = " ORDER BY \"{$sortCol}\" {$sortDir}";
        } else {
            $orderClause = '';
            $sortCol = '';
        }

        $total = (int) $this->db->fetchOne("SELECT COUNT(*) as cnt FROM \"{$table}\"")['cnt'];
        $offset = ($page - 1) * $perPage;
        $data = $this->db->fetchAll("SELECT * FROM \"{$table}\"{$orderClause} LIMIT {$perPage} OFFSET {$offset}");

        $lastPage = (int) ceil($total / $perPage);

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/dashboard-layout.html.php',
            data: [
                'title' => "FrankenForge • {$table}",
                'user' => $user,
                'navItems' => $this->navItems('database'),
                'table' => $table,
                'columnNames' => $columnNames,
                'rows' => $data,
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
                'flash' => $this->flash->all(),
                'request' => $request,
                'sortCol' => $sortCol,
                'sortDir' => $sortDir,
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
