<?php

declare(strict_types=1);

use FrankenForge\Core\Container\Container;
use FrankenForge\Core\DemoState;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Router\Router;
use FrankenForge\Core\View\View;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Dashboard\Actions\GetDashboard;

return function (Container $container): void {
    // Request is NOT a factory — create fresh instance via a builder
    // In worker mode, superglobals change per request
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

    $container->factory('router', function (Container $c) {
        $router = new Router(
            $c->get('response'),
            fn() => $c->make('request'),
        );

        $responder = $c->get('responder');
        $state = $c->get('state');
        $layoutPath = __DIR__ . '/../templates/layout.html.php';
        $viewBase = __DIR__ . '/../src/Domains/Dashboard/Views';

        $router->routes(function ($r) use ($responder, $state, $layoutPath, $viewBase) {
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

            $r->get('/dashboard', function (Request $req, Response $res) use ($responder) {
                $action = new GetDashboard($responder);
                return $action($req, $res, ['title' => 'FrankenForge • Dashboard']);
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

            // ── Toggle endpoints ──
            $r->get('/api/toggles', function (Request $req, Response $res) use ($state, $responder) {
                if ($req->wantsJson()) {
                    return $responder->json($state->toggles);
                }
                return $res->withBody(renderToggleList($state->toggles));
            });
            $r->post('/api/toggles/{id}/toggle', function (Request $req, Response $res, array $params) use ($state) {
                $id = $params['id'];
                foreach ($state->toggles as &$t) {
                    if ($t['id'] === $id) {
                        $t['enabled'] = !$t['enabled'];
                        break;
                    }
                }
                unset($t);
                return $res->withBody(renderToggleList($state->toggles));
            });

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
        $color = $t['enabled'] ? 'text-green-400' : 'text-zinc-500';
        $icon = $t['enabled'] ? 'fa-toggle-on' : 'fa-toggle-off';
        $html .= '<div class="flex items-center justify-between">';
        $html .= '<span class="' . $color . '"><i class="fa-solid ' . $icon . '"></i> ' . htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '<button hx-post="/api/toggles/' . htmlspecialchars($t['id'], ENT_QUOTES, 'UTF-8') . '/toggle"';
        $html .= ' hx-target="#toggle-list" hx-swap="innerHTML"';
        $html .= ' class="px-3 py-1 bg-zinc-800 hover:bg-zinc-700 rounded text-xs font-bold transition">Toggle</button>';
        $html .= '</div>';
    }
    return $html;
}
