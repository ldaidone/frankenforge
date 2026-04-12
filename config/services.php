<?php

declare(strict_types=1);

use FrankenForge\Core\Container\Container;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Router\Router;

return function (Container $container): void {
    $container->factory('request', fn() => new Request());
    $container->factory('response', fn() => new Response());

    $container->factory('router', function (Container $c) {
        $router = new Router(
            $c->get('request'),
            $c->get('response'),
        );

        // Define routes using HTTP verb methods
        $router->routes(function ($r) {
            $r->get('/', fn(Request $req, Response $res) => renderLandingPage($res));
            $r->get('/api/ping', fn(Request $req, Response $res) => handlePing());
        });

        return $router;
    });
};

function renderLandingPage(Response $response): void
{
    echo <<<'HTML'
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon.png">
    <title>FrankenForge • Kernel</title>
    <script src="https://unpkg.com/htmx.org@2"></script>
    <script src="https://unpkg.com/htmx-ext-sse@2.2.1/sse.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen font-mono">
    <div class="max-w-4xl mx-auto p-8">
        <h1 class="text-6xl font-bold text-orange-500 mb-4">
        <img src="/favicon_1.png" alt="FrankenForge Logo" class="inline-block w-24 h-24 mr-2 animate-pulse">
         FrankenForge
        </h1>
        <p class="text-2xl mb-8">The monster is alive.</p>

       <div id="ping-result"
            hx-ext="sse"
            sse-connect="/api/ping"
            sse-swap="message"
            class="bg-zinc-900 border border-orange-500/30 rounded-xl p-6 text-center min-h-[60px] flex items-center justify-center">
           <span class="text-green-400 font-bold">Waiting for connection...</span>
       </div>
    </div>
</body>
</html>
HTML;
}

function handlePing(): void
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');

    while (true) {
        $time = date('H:i:s');
        $html = <<<HTML
<span class="text-green-400 font-bold flex items-center justify-center gap-2">
    <i class="fa-solid fa-heart-pulse animate-pulse"></i>
    Monster is breathing at <span class="tabular-nums">{$time}</span>
</span>
HTML;

        echo "event: message\n";
        echo "data: " . str_replace("\n", "", $html) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        if (connection_aborted()) {
            break;
        }
        sleep(1);
    }
}
