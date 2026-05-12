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
 * Shows the application log viewer.
 */
final class GetLogs
{
    private const string VIEW = __DIR__ . '/../Views/logs.html.php';
    private const array LEVELS = ['debug', 'info', 'warning', 'error'];

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

        $logPath = __DIR__ . '/../../../../storage/app.log';
        $level = $request->query('level') ?? 'all';
        $page = max(1, (int) ($request->query('page') ?? 1));
        $perPage = 20;

        if (file_exists($logPath)) {
            $raw = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $raw = array_reverse($raw);

            $filtered = [];
            foreach ($raw as $line) {
                $decoded = json_decode($line, true);
                if ($decoded === null) {
                    continue;
                }
                if ($level !== 'all' && ($decoded['level'] ?? '') !== $level) {
                    continue;
                }
                $filtered[] = $decoded;
            }

            $total = count($filtered);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $page = min($page, $lastPage);
            $offset = ($page - 1) * $perPage;
            $lines = array_slice($filtered, $offset, $perPage);
        } else {
            $total = 0;
            $lastPage = 1;
            $lines = [];
        }

        $counts = ['all' => count($raw ?? []), 'debug' => 0, 'info' => 0, 'warning' => 0, 'error' => 0];
        if (!empty($raw)) {
            foreach ($raw as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null && isset($decoded['level'])) {
                    $counts[$decoded['level']] = ($counts[$decoded['level']] ?? 0) + 1;
                }
            }
        }

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/dashboard-layout.html.php',
            data: [
                'title' => 'FrankenForge • Logs',
                'user' => $user,
                'navItems' => $this->navItems('logs'),
                'lines' => $lines,
                'level' => $level,
                'levels' => self::LEVELS,
                'counts' => $counts,
                'flash' => $this->flash->all(),
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
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
            ['label' => 'Database', 'href' => '/dashboard/database', 'icon' => 'fa-database', 'active' => false],
            ['label' => 'Migrations', 'href' => '/dashboard/migrations', 'icon' => 'fa-boxes-stacked', 'active' => false],
            ['label' => 'Logs', 'href' => '/dashboard/logs', 'icon' => 'fa-file-lines', 'active' => $active === 'logs'],
            ['label' => 'Logout', 'href' => '/dashboard/logout', 'icon' => 'fa-right-from-bracket', 'active' =>false],
        ];
    }
}
