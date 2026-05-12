<?php
/**
 * FrankenForge — FrankenForge\Core\Router
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Core\Router;

use FastRoute\Dispatcher;
use FrankenForge\Core\Error\ErrorHandler;
use FrankenForge\Core\Http\MiddlewareInterface;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use function FastRoute\simpleDispatcher;

final class Router
{
    private Dispatcher $dispatcher;

    /** @var MiddlewareInterface[] */
    private array $middleware = [];

    /**
     * @param Response $response
     * @param \Closure $makeRequest
     * @param ErrorHandler|null $errorHandler Optional error handler for 404 and 405 responses
     */
    public function __construct(
        private readonly Response $response,
        private readonly \Closure $makeRequest,
        private readonly ?ErrorHandler $errorHandler = null,
    ) {}

    /**
     * Access the Response instance (for testing).
     */
    public function response(): Response
    {
        return $this->response;
    }

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
        // Fresh Request and Response for each worker request cycle
        $request = ($this->makeRequest)();
        $this->response->reset();

        $method = $request->method();
        $uri = $request->path();

        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        match ($routeInfo[0]) {
            Dispatcher::FOUND => $this->runMiddleware($routeInfo[1], $routeInfo[2] ?? [], $request),
            Dispatcher::NOT_FOUND => $this->notFound($request),
            Dispatcher::METHOD_NOT_ALLOWED => $this->methodNotAllowed($routeInfo[1]),
        };
    }

    private function notFound(Request $request): void
    {
        if ($this->errorHandler !== null) {
            $this->errorHandler->notFound($request)->send();
        } else {
            $this->response
                ->withStatus(404)
                ->withBody('404 — Page not found.')
                ->send();
        }
    }

    /**
     * Run middleware stack, then the final handler.
     *
     * @param callable $handler
     * @param array<string, string> $params
     * @param Request $request
     */
    private function runMiddleware(callable $handler, array $params, Request $request): void
    {
        $next = fn(Request $req, Response $res) => $this->invokeHandler($handler, $params, $req);

        foreach (array_reverse($this->middleware) as $mw) {
            $current = $next;
            $next = fn(Request $req, Response $res) => $mw->process($req, $res, $current);
        }

        $next($request, $this->response);

        if (!$this->response->isSent()) {
            $this->response->send();
        }
    }

    /**
     * @param callable $handler
     * @param array<string, string> $params
     * @param Request $request
     * @return Response
     */
    private function invokeHandler(callable $handler, array $params, Request $request): Response
    {
        $result = $handler($request, $this->response, $params);

        // If handler returned a different Response instance, copy its data
        if ($result instanceof Response && $result !== $this->response) {
            $this->response->withStatus($result->statusCode());
            $this->response->withBody($result->body());
            foreach ($result->header() as $name => $value) {
                $this->response->withHeader($name, $value);
            }
        }

        return $this->response;
    }

    /**
     * Handle 405 Method Not Allowed.
     *
     * @param array $allowedMethods
     */
    private function methodNotAllowed(array $allowedMethods): void
    {
        if ($this->errorHandler !== null) {
            $this->errorHandler->methodNotAllowed($allowedMethods)->send();
        } else {
            $this->response
                ->withStatus(405)
                ->withHeader('Allow', implode(', ', $allowedMethods))
                ->withBody('405 — Method not allowed.')
                ->send();
        }
    }
}
