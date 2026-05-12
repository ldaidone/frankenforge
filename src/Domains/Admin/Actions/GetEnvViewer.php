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
 * Shows the .env file viewer.
 */
final class GetEnvViewer
{
    private const string VIEW = __DIR__ . '/../Views/env-viewer.html.php';

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

        $envPath = __DIR__ . '/../../../../.env';
        $entries = [];
        $raw = '';

        if (file_exists($envPath)) {
            $raw = file_get_contents($envPath);
            foreach (preg_split('/\R/', $raw) as $i => $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    $entries[] = ['number' => $i + 1, 'line' => $line, 'key' => null, 'value' => null, 'comment' => $trimmed === '' ? '' : $trimmed];
                } else {
                    [$key, $value] = str_contains($trimmed, '=') ? explode('=', $trimmed, 2) : [$trimmed, ''];
                    $entries[] = ['number' => $i + 1, 'line' => $line, 'key' => $key, 'value' => trim($value, '"\''), 'comment' => ''];
                }
            }
        }

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/dashboard-layout.html.php',
            data: [
                'title' => 'FrankenForge • Environment',
                'user' => $user,
                'navItems' => $this->navItems('env'),
                'entries' => $entries,
                'raw' => $raw,
                'path' => $envPath,
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
            ['label' => 'Environment', 'href' => '/dashboard/env', 'icon' => 'fa-key', 'active' => $active === 'env'],
            ['label' => 'Database', 'href' => '/dashboard/database', 'icon' => 'fa-database', 'active' => false],
            ['label' => 'Migrations', 'href' => '/dashboard/migrations', 'icon' => 'fa-boxes-stacked', 'active' => false],
            ['label' => 'Logs', 'href' => '/dashboard/logs', 'icon' => 'fa-file-lines', 'active' => false],
            ['label' => 'Logout', 'href' => '/dashboard/logout', 'icon' => 'fa-right-from-bracket', 'active' =>false],
        ];
    }
}
