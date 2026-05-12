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
 * Shows the system overview page.
 */
final class GetOverview
{
    private const string VIEW = __DIR__ . '/../Views/overview.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response, array $params = []): Response
    {
        $user = $this->auth->user();

        if ($user === null) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        if ($user->mustChangePassword) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/password');
        }

        $info = $this->collectSystemInfo();

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/dashboard-layout.html.php',
            data: [
                'title' => 'FrankenForge • Overview',
                'user' => $user,
                'navItems' => $this->navItems('overview'),
                'info' => $info,
                'flash' => $this->flash->all(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function collectSystemInfo(): array
    {
        $memory = memory_get_peak_usage(true);

        return [
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'frankenphp' => extension_loaded('frankenphp') ? 'yes' : 'no',
            'memory' => $this->formatBytes($memory),
            'memory_bytes' => $memory,
            'extensions' => get_loaded_extensions(),
            'ini_file' => php_ini_loaded_file() ?: 'none',
            'opcache' => ini_get('opcache.enable') ? 'enabled' : 'disabled',
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * @return list<array{label: string, href: string, icon: string, active: bool, divider?: bool}>
     */
    private function navItems(string $active): array
    {
        return [
            ['label' => 'Overview', 'href' => '/dashboard/overview', 'icon' => 'fa-gauge-high', 'active' => $active === 'overview'],
            ['label' => 'Profile', 'href' => '/dashboard/profile', 'icon' => 'fa-user', 'active' => $active === 'profile'],
            ['label' => 'Environment', 'href' => '/dashboard/env', 'icon' => 'fa-key', 'active' => $active === 'env'],
            ['label' => 'Database', 'href' => '/dashboard/database', 'icon' => 'fa-database', 'active' => $active === 'database'],
            ['label' => 'Migrations', 'href' => '/dashboard/migrations', 'icon' => 'fa-boxes-stacked', 'active' => $active === 'migrations'],
            ['label' => 'Logs', 'href' => '/dashboard/logs', 'icon' => 'fa-file-lines', 'active' => $active === 'logs'],
            ['label' => 'Logout', 'href' => '/dashboard/logout', 'icon' => 'fa-right-from-bracket', 'active' =>false],
        ];
    }
}
