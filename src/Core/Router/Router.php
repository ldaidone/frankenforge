<?php

declare(strict_types=1);

namespace FrankenForge\Core\Router;

use FastRoute\Dispatcher;
use FrankenForge\Core\Http\MiddlewareInterface;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use function FastRoute\simpleDispatcher;

final class Router
{
    private Dispatcher $dispatcher;

    /** @var MiddlewareInterface[] */
    private array $middleware = [];

    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
    ) {}

    /**
     * Define routes. Called once during construction or wiring.
     *
     * @param callable(object): void $routes
     */
    public function routes(callable $routes): void
    {
        $this->dispatcher = simpleDispatcher(function ($r) use ($routes) {
            // Expose convenience methods to the route definition callback
            $router = new class($r) {
                public function __construct(private object $routeCollector) {}

                public function get(string $path, callable $handler): void
                {
                    $this->routeCollector->addRoute('GET', $path, $handler);
                }

                public function post(string $path, callable $handler): void
                {
                    $this->routeCollector->addRoute('POST', $path, $handler);
                }

                public function put(string $path, callable $handler): void
                {
                    $this->routeCollector->addRoute('PUT', $path, $handler);
                }

                public function delete(string $path, callable $handler): void
                {
                    $this->routeCollector->addRoute('DELETE', $path, $handler);
                }

                public function patch(string $path, callable $handler): void
                {
                    $this->routeCollector->addRoute('PATCH', $path, $handler);
                }

                public function any(string $path, callable $handler): void
                {
                    foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
                        $this->routeCollector->addRoute($method, $path, $handler);
                    }
                }
            };

            $routes($router);
        });
    }

    /**
     * Register global middleware (applied to every request in order).
     */
    public function middleware(MiddlewareInterface ...$middleware): void
    {
        $this->middleware = [...$this->middleware, ...$middleware];
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $uri = $this->request->path();

        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        match ($routeInfo[0]) {
            Dispatcher::FOUND => $this->runMiddleware($routeInfo[1], $routeInfo[2] ?? []),
            Dispatcher::NOT_FOUND => $this->response->withBody('404 — FrankenForge has no route for this path yet.')->withStatus(404)->send(),
            Dispatcher::METHOD_NOT_ALLOWED => $this->methodNotAllowed($routeInfo[1]),
        };
    }

    /**
     * Run middleware stack, then the final handler.
     *
     * @param callable $handler
     * @param array<string, string> $params
     */
    private function runMiddleware(callable $handler, array $params): void
    {
        $next = fn(Request $req, Response $res) => $this->invokeHandler($handler, $params);

        foreach (array_reverse($this->middleware) as $mw) {
            $current = $next;
            $next = fn(Request $req, Response $res) => $mw->process($req, $res, $current);
        }

        $next($this->request, $this->response);

        if (!$this->response->isSent()) {
            $this->response->send();
        }
    }

    /**
     * @param callable $handler
     * @param array<string, string> $params
     */
    private function invokeHandler(callable $handler, array $params): Response
    {
        $handler($this->request, $this->response, $params);
        return $this->response;
    }

    private function methodNotAllowed(array $allowedMethods): void
    {
        $this->response
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $allowedMethods))
            ->withBody('405 — Method not allowed.')
            ->send();
    }
}
