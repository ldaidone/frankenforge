<?php

declare(strict_types=1);

namespace FrankenForge\Core\Router;

final class Router
{
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        match ($uri) {
            '/', '/index.php' => $this->renderLandingPage(),
            '/api/ping' => $this->handlePing(),
            default => $this->notFound(),
        };
    }

    private function renderLandingPage(): void
    {
        echo <<<'HTML'
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrankenForge • Kernel</title>
    <script src="https://unpkg.com/htmx.org@2"></script>
    <script src="https://unpkg.com/htmx-ext-sse@2.2.1/sse.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen font-mono">
    <div class="max-w-4xl mx-auto p-8">
        <h1 class="text-6xl font-bold text-orange-500 mb-4">🔥 FrankenForge</h1>
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

    private function handlePing(): void
    {
        // 1. Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Essential for Nginx/Proxy setups

        while (true) {
            // 2. Generate the content
            $time = date('H:i:s');
            $html = <<<HTML
<span class="text-green-400 font-bold flex items-center justify-center gap-2">
    <i class="fa-solid fa-heart-pulse animate-pulse"></i>
    Monster is breathing at <span class="tabular-nums">{$time}</span>
</span>
HTML;

            // 3. Format as an SSE message
            // HTMX looks for "message" events by default if sse-swap="message"
            echo "event: message\n";
            echo "data: " . str_replace("\n", "", $html) . "\n\n";

            // 4. Flush the buffer to send data immediately
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            // 5. Break if the connection is lost or wait for next tick
            if (connection_aborted()) break;
            sleep(1);
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 — FrankenForge has no route for this path yet.';
    }
}
