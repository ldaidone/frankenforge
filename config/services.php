<?php
/**
 * FrankenForge — frankenforge/kernel/config
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */

declare(strict_types=1);

use FrankenForge\Core\Container\Container;
use FrankenForge\Core\DemoState;
use FrankenForge\Core\Error\ErrorHandler;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Logging\FileLogger;
use FrankenForge\Core\Responders\JsonResponder;
use FrankenForge\Core\Router\Router;
use FrankenForge\Core\Session\FlashMessages;
use FrankenForge\Core\View\Responder;
use FrankenForge\Core\View\View;
use FrankenForge\Domains\Admin\Actions\GetEnvViewer;
use FrankenForge\Domains\Admin\Actions\GetLogin;
use FrankenForge\Domains\Admin\Actions\GetLogs;
use FrankenForge\Domains\Admin\Actions\GetMigrations;
use FrankenForge\Domains\Admin\Actions\GetOverview;
use FrankenForge\Domains\Admin\Actions\GetPasswordChange;
use FrankenForge\Domains\Admin\Actions\GetProfile;
use FrankenForge\Domains\Admin\Actions\GetTableView;
use FrankenForge\Domains\Admin\Actions\GetTableViewer;
use FrankenForge\Domains\Admin\Actions\PostEnvSave;
use FrankenForge\Domains\Admin\Actions\PostLogin;
use FrankenForge\Domains\Admin\Actions\PostLogout;
use FrankenForge\Domains\Admin\Actions\PostMigrationRun;
use FrankenForge\Domains\Admin\Actions\PostPasswordChange;
use FrankenForge\Domains\Admin\Actions\PostProfile;
use FrankenForge\Domains\Admin\Http\AuthMiddleware;
use FrankenForge\Domains\Admin\Repositories\AdminUserRepositoryInterface;
use FrankenForge\Domains\Admin\Services\Auth;
use FrankenForge\Domains\Admin\Services\PasswordHasher;
use FrankenForge\Domains\Dashboard\Actions\GetDashboardStats;
use FrankenForge\Domains\Dashboard\Actions\GetDemoToggles;
use FrankenForge\Domains\Dashboard\Actions\GetFlashMessages;
use FrankenForge\Domains\Dashboard\Actions\GetInvoicesTable;
use FrankenForge\Domains\Dashboard\Actions\GetUsersTable;
use FrankenForge\Domains\Dashboard\Actions\ToggleFeature;
use FrankenForge\Domains\Dashboard\Repositories\InvoiceRepositoryInterface;
use FrankenForge\Domains\Dashboard\Repositories\StatsRepositoryInterface;
use FrankenForge\Domains\Dashboard\Repositories\ToggleRepositoryInterface;
use FrankenForge\Shared\Infrastructure\Database\Connection;
use FrankenForge\Shared\Infrastructure\Database\SqliteAdminUserRepository;
use FrankenForge\Shared\Infrastructure\Database\SqliteInvoiceRepository;
use FrankenForge\Shared\Infrastructure\Database\SqliteStatsRepository;
use FrankenForge\Shared\Infrastructure\Database\SqliteToggleRepository;

return function (Container $container): void {
    // ── Infrastructure ──────────────────────────────────────
    $container->factory('db', function () {
        $dsn = $_ENV['DATABASE_URL'] ?? 'sqlite:' . __DIR__ . '/../storage/app.db';
        return new Connection($dsn);
    });

    $container->factory(StatsRepositoryInterface::class, function (Container $c) {
        return new SqliteStatsRepository($c->get('db'));
    });

    $container->factory(InvoiceRepositoryInterface::class, function (Container $c) {
        return new SqliteInvoiceRepository($c->get('db'));
    });

    $container->factory(ToggleRepositoryInterface::class, function (Container $c) {
        return new SqliteToggleRepository($c->get('db'));
    });

    // ── Auth ────────────────────────────────────────────────
    $container->factory(PasswordHasher::class, fn() => new PasswordHasher());

    $container->factory(AdminUserRepositoryInterface::class, function (Container $c) {
        return new SqliteAdminUserRepository($c->get('db'), $c->get(PasswordHasher::class));
    });

    $container->factory(Auth::class, function (Container $c) {
        return new Auth($c->get(AdminUserRepositoryInterface::class));
    });

    // ── Core ────────────────────────────────────────────────
    $container->factory('logger', fn() => new FileLogger(__DIR__ . '/../storage/app.log'));
    $container->factory('flash', fn() => new FlashMessages());
    $container->factory(JsonResponder::class, fn(Container $c) => new JsonResponder($c->get('response')));
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

    // ── Router ─────────────────────────────────────────────
    $container->factory('router', function (Container $c) {
        $router = new Router(
            $c->get('response'),
            fn() => $c->make('request'),
            $c->get('errorHandler'),
        );

        // ─ Middleware ──
        $router->middleware(new AuthMiddleware($c->get(Auth::class)));

        $responder = $c->get('responder');
        $auth = $c->get(Auth::class);
        $flash = $c->get('flash');
        $jsonResponder = $c->get(JsonResponder::class);
        $statsRepo = $c->get(StatsRepositoryInterface::class);
        $usersRepo = $c->get(AdminUserRepositoryInterface::class);
        $togglesRepo = $c->get(ToggleRepositoryInterface::class);
        $state = $c->get('state');
        $layoutPath = __DIR__ . '/../templates/layout.html.php';
        $dashLayoutPath = __DIR__ . '/../templates/dashboard-layout.html.php';
        $viewBase = __DIR__ . '/../src/Domains/Dashboard/Views';

        $router->routes(function ($r) use (
            $responder, $auth, $flash, $jsonResponder,
            $statsRepo, $usersRepo, $togglesRepo, $state,
            $layoutPath, $dashLayoutPath, $viewBase, $c
        ) {
            // ─ Landing page (public) ──
            $r->get('/', fn(Request $req, Response $res) => $responder->respond(
                viewPath: "{$viewBase}/landing.html.php",
                layoutPath: $layoutPath,
                data: ['title' => 'FrankenForge • Kernel'],
                json: fn() => ['status' => 'ok', 'message' => 'FrankenForge is alive'],
            ));

            // ── Legacy demo pages (public) ──
            $r->get('/demo', fn(Request $req, Response $res) => $responder->respond(
                viewPath: "{$viewBase}/demo.html.php",
                layoutPath: $layoutPath,
                data: ['title' => 'FrankenForge • Demo'],
            ));
            $r->get('/demo/toggles', function (Request $req, Response $res) use ($responder, $togglesRepo) {
                $action = new GetDemoToggles($responder, $togglesRepo);
                return $action($req, $res);
            });
            $r->post('/dashboard/toggle/{feature}', function (Request $req, Response $res, array $params) use ($responder, $togglesRepo) {
                $action = new ToggleFeature($responder, $togglesRepo);
                return $action($req, $res, $params);
            });

            // ── API endpoints (public) ─
            $r->get('/api/stats', function (Request $req, Response $res) use ($responder, $statsRepo, $jsonResponder) {
                $stats = array_map(fn($s) => [
                    'key' => $s->key, 'label' => $s->label, 'value' => $s->value,
                    'icon' => $s->icon, 'trend' => $s->trend, 'up' => $s->up,
                ], $statsRepo->findAll());
                return $jsonResponder->respond($stats);
            });
            $r->get('/api/toggles', function (Request $req, Response $res) use ($togglesRepo, $jsonResponder) {
                return $jsonResponder->respond($togglesRepo->findAll());
            });
            $r->post('/api/toggles/{id}/toggle', function (Request $req, Response $res, array $params) use ($togglesRepo, $jsonResponder) {
                $result = $togglesRepo->toggle($params['id']);
                if (!$result['success']) return $jsonResponder->error($result['error'], 404);
                return $jsonResponder->respond(['id' => $result['id'], 'enabled' => $result['enabled']]);
            });
            $r->get('/api/counter', fn(Request $req, Response $res) => $res->withBody((string) $state->counter));
            $r->post('/api/counter/increment', fn(Request $req, Response $res) => $res->withBody((string) ++$state->counter));
            $r->post('/api/counter/reset', fn(Request $req, Response $res) => $res->withBody((string) ($state->counter = 0)));
            $r->get('/api/ping', function (Request $req, Response $res) use ($responder, $viewBase) {
                session_write_close(); // Release session lock for long-running SSE
                $userTz = $_GET['tz'] ?? 'UTC'; // Default a UTC por seguridad

                try {
                    date_default_timezone_set($userTz);
                } catch (Exception $e) {
                    date_default_timezone_set('UTC');
                }
                if ($req->wantsJson()) {
                    return $responder->json(['time' => date('H:i:s'), 'status' => 'breathing']);
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
                return $res;
            });

            // ── Legacy HTMX fragments (protected) ──
            $r->get('/dashboard/stats', function (Request $req, Response $res) use ($responder, $statsRepo) {
                $action = new GetDashboardStats($responder, $statsRepo);
                return $action($req, $res);
            });
            $r->get('/dashboard/users', function (Request $req, Response $res) use ($responder, $c) {
                $action = new GetUsersTable($responder, $c->get(\FrankenForge\Domains\Dashboard\Repositories\UserRepositoryInterface::class));
                return $action($req, $res);
            });
            $r->get('/dashboard/invoices', function (Request $req, Response $res) use ($responder, $c) {
                $action = new GetInvoicesTable($responder, $c->get(InvoiceRepositoryInterface::class));
                return $action($req, $res);
            });
            $r->get('/dashboard', fn(Request $req, Response $res) => $res->withStatus(302)->withHeader('Location', '/dashboard/overview'));
            $r->post('/flash/{type}', function (Request $req, Response $res, array $params) use ($responder, $c) {
                $action = new GetFlashMessages($responder, $c->get('flash'));
                return $action($req, $res, $params);
            });

            // ── Admin Auth ──
            $r->get('/dashboard/login', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetLogin($responder, $auth, $flash);
                return $action($req, $res);
            });
            $r->post('/dashboard/login', function (Request $req, Response $res) use ($auth, $flash) {
                $action = new PostLogin($auth, $flash);
                return $action($req, $res);
            });
            $r->get('/dashboard/logout', function (Request $req, Response $res) use ($auth, $flash) {
                $action = new PostLogout($auth, $flash);
                return $action($req, $res);
            });
            $r->get('/dashboard/password', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetPasswordChange($responder, $auth, $flash);
                return $action($req, $res);
            });
            $r->post('/dashboard/password', function (Request $req, Response $res) use ($c, $auth, $flash) {
                $action = new PostPasswordChange(
                    $auth,
                    $c->get(PasswordHasher::class),
                    $c->get(AdminUserRepositoryInterface::class),
                    $flash,
                );
                return $action($req, $res);
            });

            // ── Admin Dashboard ──
            $r->get('/dashboard/overview', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetOverview($responder, $auth, $flash);
                return $action($req, $res);
            });
            $r->get('/dashboard/profile', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetProfile($responder, $auth, $flash);
                return $action($req, $res);
            });
            $r->post('/dashboard/profile', function (Request $req, Response $res) use ($c, $auth, $flash) {
                $action = new PostProfile($auth, $c->get(AdminUserRepositoryInterface::class), $flash);
                return $action($req, $res);
            });

            // ── Admin Tools ──
            $r->get('/dashboard/env', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetEnvViewer($responder, $auth, $flash);
                return $action($req, $res);
            });
            $r->post('/dashboard/env/save', function (Request $req, Response $res) use ($auth, $flash) {
                $action = new PostEnvSave($auth, $flash);
                return $action($req, $res);
            });
            $r->get('/dashboard/database', function (Request $req, Response $res) use ($responder, $auth, $flash, $c) {
                $action = new GetTableViewer($responder, $auth, $flash, $c->get('db'));
                return $action($req, $res);
            });
            $r->get('/dashboard/database/{table}', function (Request $req, Response $res, array $params) use ($responder, $auth, $flash, $c) {
                $action = new GetTableView($responder, $auth, $flash, $c->get('db'));
                return $action($req, $res, $params);
            });
            $r->get('/dashboard/migrations', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetMigrations($responder, $auth, $flash);
                return $action($req, $res);
            });
            $r->post('/dashboard/migrations/run', function (Request $req, Response $res) use ($auth, $flash) {
                $action = new PostMigrationRun($auth, $flash);
                return $action($req, $res);
            });
            $r->get('/dashboard/logs', function (Request $req, Response $res) use ($responder, $auth, $flash) {
                $action = new GetLogs($responder, $auth, $flash);
                return $action($req, $res);
            });

            // ─ Debug ──
            $r->get('/throw-error', fn() => throw new \RuntimeException('Test 500'));
        });

        return $router;
    });
};
