<?php

declare(strict_types=1);

namespace FrankenForge\Tests\Unit\Core\Router;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Router\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private function makeRouter(): Router
    {
        return new Router(new Response(), fn() => new Request());
    }

    #[Test]
    public function it_registers_and_dispatches_get_routes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello';
        $_SERVER['QUERY_STRING'] = '';

        $router = $this->makeRouter();

        $router->routes(function ($r) {
            $r->get('/hello', fn(Request $req, Response $res) => $res->withBody('hello'));
        });

        $router->dispatch();

        self::assertSame('hello', $router->response()->body());
    }

    #[Test]
    public function it_returns_404_for_unknown_routes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';
        $_SERVER['QUERY_STRING'] = '';

        $router = $this->makeRouter();

        $router->routes(function ($r) {
            $r->get('/', fn() => null);
        });

        $router->dispatch();

        self::assertSame(404, $router->response()->statusCode());
        self::assertStringContainsString('404', $router->response()->body());
    }

    #[Test]
    public function it_extracts_route_parameters(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/users/42';
        $_SERVER['QUERY_STRING'] = '';

        $router = $this->makeRouter();

        $router->routes(function ($r) {
            $r->get('/users/{id}', fn(Request $req, Response $res, array $params) => $res->withBody($params['id']));
        });

        $router->dispatch();

        self::assertSame('42', $router->response()->body());
    }

    #[Test]
    public function it_registers_post_routes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/submit';
        $_SERVER['QUERY_STRING'] = '';

        $router = $this->makeRouter();

        $router->routes(function ($r) {
            $r->post('/submit', fn(Request $req, Response $res) => $res->withBody('created'));
        });

        $router->dispatch();

        self::assertSame('created', $router->response()->body());
    }

    #[Test]
    public function it_returns_405_for_wrong_method(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/only-get';
        $_SERVER['QUERY_STRING'] = '';

        $router = $this->makeRouter();

        $router->routes(function ($r) {
            $r->get('/only-get', fn() => null);
        });

        $router->dispatch();

        self::assertSame(405, $router->response()->statusCode());
        self::assertStringContainsString('GET', $router->response()->header('Allow') ?? '');
    }
}
