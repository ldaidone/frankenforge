<?php

declare(strict_types=1);

use FrankenForge\Core\Container\Container;
use FrankenForge\Core\DemoState;
use FrankenForge\Core\Error\ErrorHandler;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Http\Middleware\CsrfMiddleware;
use FrankenForge\Core\Logging\FileLogger;
use FrankenForge\Core\Responders\JsonResponder;
use FrankenForge\Core\Router\Router;
use FrankenForge\Core\Security\CsrfToken;
use FrankenForge\Core\Session\FlashMessages;
use FrankenForge\Core\View\View;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Dashboard\Actions\GetDashboard;
use FrankenForge\Domains\Dashboard\Actions\GetDashboardStats;
use FrankenForge\Domains\Dashboard\Actions\GetFlashMessages;
use FrankenForge\Domains\Dashboard\Actions\GetInvoicesTable;
use FrankenForge\Domains\Dashboard\Actions\GetUsersTable;
use FrankenForge\Domains\Dashboard\Actions\ToggleFeature;
use FrankenForge\Domains\Dashboard\Repositories\InvoiceRepositoryInterface;
use FrankenForge\Domains\Dashboard\Repositories\StatsRepositoryInterface;
use FrankenForge\Domains\Dashboard\Repositories\ToggleRepositoryInterface;
use FrankenForge\Domains\Dashboard\Repositories\UserRepositoryInterface;
use FrankenForge\Shared\Infrastructure\Database\Connection;
use FrankenForge\Shared\Infrastructure\Database\SqliteInvoiceRepository;
use FrankenForge\Shared\Infrastructure\Database\SqliteStatsRepository;
use FrankenForge\Shared\Infrastructure\Database\SqliteToggleRepository;
use FrankenForge\Shared\Infrastructure\Database\SqliteUserRepository;

return function (Container $container): void {
    // ── Infrastructure ───────────────────────────────
    $container->factory('db', function () {
        $dsn = $_ENV['DATABASE_URL'] ?? 'sqlite:' . __DIR__ . '/../storage/app.db';
        return new Connection($dsn);
    });

    $container->factory(StatsRepositoryInterface::class, function (Container $c) {
        return new SqliteStatsRepository($c->get('db'));
    });

    $container->factory(UserRepositoryInterface::class, function (Container $c) {
        return new SqliteUserRepository($c->get('db'));
    });

    $container->factory(InvoiceRepositoryInterface::class, function (Container $c) {
        return new SqliteInvoiceRepository($c->get('db'));
    });

    $container->factory(ToggleRepositoryInterface::class, function (Container $c) {
        return new SqliteToggleRepository($c->get('db'));
    });

    // ── Security ───────────────────────────────
    $container->factory('csrf', fn() => new CsrfToken());
    $container->factory(JsonResponder::class, fn(Container $c) => new JsonResponder($c->get('response')));
    $container->factory('logger', fn() => new FileLogger(__DIR__ . '/../storage/app.log'));

    // ── Session ───────────────────────────────
    $container->factory('flash', fn() => new FlashMessages());

    // Request is NOT a factory — create fresh instance via a builder
    $container->factory('request', fn() => new Request());
    $container->factory('response', fn() => new Response());
    $container->factory('view', fn() => new View());
    $container->factory('state', fn() => new DemoState());
    $container->factory('responder', function (Container $c) {
        return new Responder(
            fn() => $c->make('request'),
            $c->get('response'),
            $c->get('view'),
        );
    });

    $container->factory('errorHandler', function (Container $c) {
        return new ErrorHandler(
            $c->get('view'),
            __DIR__ . '/../templates',
        );
    });

    $container->factory('router', function (Container $c) {
        $router = new Router(
            $c->get('response'),
            fn() => $c->make('request'),
            $c->get('errorHandler'),
        );

        // Apply CSRF middleware (optional for API - disabled for now)
        // $router->middleware(new CsrfMiddleware($c->get('csrf')));

        $responder = $c->get('responder');
        $state = $c->get('state');
        $statsRepo = $c->get(StatsRepositoryInterface::class);
        $usersRepo = $c->get(UserRepositoryInterface::class);
        $invoicesRepo = $c->get(InvoiceRepositoryInterface::class);
        $togglesRepo = $c->get(ToggleRepositoryInterface::class);
        $jsonResponder = $c->get(JsonResponder::class);
        $layoutPath = __DIR__ . '/../templates/layout.html.php';
        $viewBase = __DIR__ . '/../src/Domains/Dashboard/Views';

        $router->routes(function ($r) use ($responder, $state, $statsRepo, $usersRepo, $invoicesRepo, $togglesRepo, $jsonResponder, $layoutPath, $viewBase, $c) {
            $r->get('/demo', fn(Request $req, Response $res) => $responder->respond(
                viewPath: "{$viewBase}/demo.html.php",
                layoutPath: $layoutPath,
                data: ['title' => 'FrankenForge • Demo'],
            ));

            $r->get('/', fn(Request $req, Response $res) => $responder->respond(
                viewPath: "{$viewBase}/landing.html.php",
                layoutPath: $layoutPath,
                data: ['title' => 'FrankenForge • Kernel'],
                json: fn() => ['status' => 'ok', 'message' => 'FrankenForge is alive'],
            ));

            $r->get('/dashboard', function (Request $req, Response $res) use ($responder, $statsRepo, $c) {
                $action = new GetDashboard($responder, $statsRepo, $c->get('flash'));
                return $action($req, $res, ['title' => 'FrankenForge • Dashboard']);
            });

            // ── API JSON endpoints ──
            $r->get('/api/stats', function (Request $req, Response $res) use ($responder, $statsRepo, $jsonResponder) {
                $stats = array_map(fn($s) => [
                    'key' => $s->key,
                    'label' => $s->label,
                    'value' => $s->value,
                    'icon' => $s->icon,
                    'trend' => $s->trend,
                    'up' => $s->up,
                ], $statsRepo->findAll());
                return $jsonResponder->respond($stats);
            });
            $r->get('/api/users', function (Request $req, Response $res) use ($responder, $usersRepo, $jsonResponder) {
                $users = array_map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                ], $usersRepo->findAll());
                return $jsonResponder->respond($users);
            });
            $r->get('/api/toggles', function (Request $req, Response $res) use ($responder, $togglesRepo, $jsonResponder) {
                return $jsonResponder->respond($togglesRepo->findAll());
            });
            $r->post('/api/toggles/{id}/toggle', function (Request $req, Response $res, array $params) use ($responder, $togglesRepo, $jsonResponder) {
                $result = $togglesRepo->toggle($params['id']);
                if (!$result['success']) {
                    return $jsonResponder->error($result['error'], 404);
                }
                return $jsonResponder->respond([
                    'id' => $result['id'],
                    'enabled' => $result['enabled'],
                ]);
            });

            // ── HTMX fragment endpoints ──
            $r->get('/dashboard/stats', function (Request $req, Response $res) use ($responder, $statsRepo) {
                $action = new GetDashboardStats($responder, $statsRepo);
                return $action($req, $res);
            });
            $r->get('/dashboard/users', function (Request $req, Response $res) use ($responder, $usersRepo) {
                $action = new GetUsersTable($responder, $usersRepo);
                return $action($req, $res);
            });
            $r->get('/dashboard/invoices', function (Request $req, Response $res) use ($responder, $invoicesRepo) {
                $action = new GetInvoicesTable($responder, $invoicesRepo);
                return $action($req, $res);
            });
            $r->post('/dashboard/toggle/{feature}', function (Request $req, Response $res, array $params) use ($responder) {
                $action = new ToggleFeature($responder);
                return $action($req, $res, $params);
            });

            // ── Flash message endpoints ──
            $r->post('/flash/{type}', function (Request $req, Response $res, array $params) use ($responder, $c) {
                $action = new GetFlashMessages($responder, $c->get('flash'));
                return $action($req, $res, $params);
            });

            // ── Counter endpoints ──
            $r->get('/api/counter', function (Request $req, Response $res) use ($state) {
                return $res->withBody((string) $state->counter);
            });
            $r->post('/api/counter/increment', function (Request $req, Response $res) use ($state) {
                $state->counter++;
                return $res->withBody((string) $state->counter);
            });
            $r->post('/api/counter/reset', function (Request $req, Response $res) use ($state) {
                $state->counter = 0;
                return $res->withBody('0');
            });

            // ── Toggle endpoints (using DB) ──
            // Note: These routes are now at /api/toggles above
            // Keeping old state-based routes removed for DB consistency

            // ── SSE ping ──
            $r->get('/api/ping', function (Request $req, Response $res) use ($responder, $viewBase) {
                if ($req->wantsJson()) {
                    return $responder->respond(
                        viewPath: "{$viewBase}/ping.html.php",
                        layoutPath: null,
                        data: ['time' => date('H:i:s')],
                        json: fn() => ['time' => date('H:i:s'), 'status' => 'breathing'],
                    );
                }

                header('Content-Type: text/event-stream');
                header('Cache-Control: no-cache');
                header('Connection: keep-alive');
                header('X-Accel-Buffering: no');

                while (true) {
                    $time = date('H:i:s');
                    $html = '<span class="text-green-400 font-bold flex items-center justify-center gap-2"><i class="fa-solid fa-heart-pulse animate-pulse"></i>Monster is breathing at <span class="tabular-nums">' . $time . '</span></span>';

                    echo "event: message\n";
                    echo "data: " . $html . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    if (connection_aborted()) break;
                    sleep(1);
                }
            });

            $r->get('/throw-error', fn() => throw new \RuntimeException('Test 500'));
        });

        return $router;
    });
};

/**
 * @param array<array{id:string, label:string, enabled:bool}> $toggles
 */
function renderToggleList(array $toggles): string
{
    $html = '';
    foreach ($toggles as $t) {
        $color = $t['enabled'] ? '#4ade80' : '#a3a3a3';
        $icon = $t['enabled'] ? 'fa-toggle-on' : 'fa-toggle-off';
        $bg = 'var(--app-quick-link, #1e293b)';
        $bgHover = 'var(--app-quick-link-hover, #334155)';
        $html .= '<div class="flex items-center justify-between py-2">';
        $html .= '<span style="color:' . $color . '"><i class="fa-solid ' . $icon . '"></i> ' . htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '<button hx-post="/api/toggles/' . htmlspecialchars($t['id'], ENT_QUOTES, 'UTF-8') . '/toggle"';
        $html .= ' hx-target="#toggle-list" hx-swap="innerHTML"';
        $html .= ' style="background:' . $bg . '" onmouseover="this.style.background=\'' . $bgHover . '\'" onmouseout="this.style.background=\'' . $bg . '\'"';
        $html .= ' class="px-3 py-1 rounded text-xs font-bold text-zinc-100 transition">Toggle</button>';
        $html .= '</div>';
    }
    return $html;
}

