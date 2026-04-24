<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Dashboard\Repositories\StatsRepositoryInterface;

/**
 * Renders the dashboard landing page with sidebar layout.
 *
 * Demonstrates the Action pattern: a single callable class
 * that prepares data and delegates to the Responder.
 */
final class GetDashboard
{
    private const string LAYOUT = __DIR__ . '/../../../../templates/dashboard-layout.html.php';
    private const string VIEW   = __DIR__ . '/../Views/overview.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly StatsRepositoryInterface $statsRepo,
        private readonly \FrankenForge\Core\Session\FlashMessages $flash,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function __invoke(Request $request, Response $response, array $data = []): Response
    {
        $stats = $this->statsRepo->findAll();

        $viewData = [
            'title'     => $data['title'] ?? 'FrankenForge • Dashboard',
            'brandLabel' => 'FrankenForge',
            'navItems'  => $data['navItems'] ?? $this->defaultNavItems('/dashboard'),
            'stats'    => $stats,
            'flash'    => $this->flash->all(),
            ...$data,
        ];

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: self::LAYOUT,
            data: $viewData,
        );
    }

    /**
     * @return array<int, array{label:string, href:string, icon:string, active:bool}|array{divider:true}>
     */
    private function defaultNavItems(string $currentPath): array
    {
        return [
            ['label' => 'Overview',    'href' => '/dashboard',       'icon' => 'fa-grid-2',  'active' => $currentPath === '/dashboard'],
            ['label' => 'Demo',        'href' => '/demo',            'icon' => 'fa-bolt',    'active' => false],
            ['divider' => true],
            ['label' => 'Landing',     'href' => '/',                'icon' => 'fa-house',   'active' => false],
        ];
    }
}
