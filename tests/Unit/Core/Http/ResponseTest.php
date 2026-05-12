<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Tests\Unit\Core\Http;

use FrankenForge\Core\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    #[Test]
    public function it_creates_response_with_defaults(): void
    {
        $r = new Response();

        self::assertSame(200, $r->statusCode());
        self::assertSame('', $r->body());
        self::assertNull($r->header('Content-Type'));
    }

    #[Test]
    public function it_creates_response_with_constructor_args(): void
    {
        $r = new Response('hello', 201, ['X-Custom' => 'val']);

        self::assertSame(201, $r->statusCode());
        self::assertSame('hello', $r->body());
        self::assertSame('val', $r->header('X-Custom'));
    }

    #[Test]
    public function it_sets_status_code_via_withStatus(): void
    {
        $r = new Response();
        $r->withStatus(404);

        self::assertSame(404, $r->statusCode());
    }

    #[Test]
    public function withStatus_is_fluent(): void
    {
        $r = new Response();
        $ret = $r->withStatus(201);

        self::assertSame($r, $ret);
    }

    #[Test]
    public function it_sets_and_gets_body(): void
    {
        $r = new Response();
        $r->withBody('content');

        self::assertSame('content', $r->body());
    }

    #[Test]
    public function withBody_is_fluent(): void
    {
        $r = new Response();
        $ret = $r->withBody('x');

        self::assertSame($r, $ret);
    }

    #[Test]
    public function it_sets_and_gets_headers_case_insensitively(): void
    {
        $r = new Response();
        $r->withHeader('Content-Type', 'text/html');

        self::assertSame('text/html', $r->header('content-type'));
        self::assertSame('text/html', $r->header('Content-Type'));
    }

    #[Test]
    public function withHeader_is_fluent(): void
    {
        $r = new Response();
        $ret = $r->withHeader('X-Foo', 'bar');

        self::assertSame($r, $ret);
    }

    #[Test]
    public function it_returns_null_for_missing_header(): void
    {
        $r = new Response();

        self::assertNull($r->header('X-Nope'));
    }

    #[Test]
    public function it_returns_all_headers(): void
    {
        $r = new Response('', 200, ['A' => '1', 'B' => '2']);

        self::assertSame(['A' => '1', 'B' => '2'], $r->header());
    }

    #[Test]
    public function it_removes_header_by_name_case_insensitively(): void
    {
        $r = new Response('', 200, ['X-Foo' => 'bar']);
        $r->withoutHeader('x-foo');

        self::assertNull($r->header('X-Foo'));
    }

    #[Test]
    public function withoutHeader_is_fluent(): void
    {
        $r = new Response('', 200, ['X-Foo' => 'bar']);
        $ret = $r->withoutHeader('X-Foo');

        self::assertSame($r, $ret);
    }

    #[Test]
    public function static_make_factory(): void
    {
        $r = Response::make('body', 201, ['X-Custom' => 'v']);

        self::assertSame('body', $r->body());
        self::assertSame(201, $r->statusCode());
        self::assertSame('v', $r->header('X-Custom'));
    }

    #[Test]
    public function static_json_factory(): void
    {
        $r = Response::json(['key' => 'value'], 201);

        self::assertSame(201, $r->statusCode());
        self::assertSame('application/json', $r->header('Content-Type'));
        self::assertJsonStringEqualsJsonString(
            '{"key":"value"}',
            $r->body()
        );
    }

    #[Test]
    public function static_html_factory(): void
    {
        $r = Response::html('<h1>Hello</h1>');

        self::assertSame(200, $r->statusCode());
        self::assertStringContainsString('text/html', (string) $r->header('Content-Type'));
        self::assertSame('<h1>Hello</h1>', $r->body());
    }

    #[Test]
    public function static_redirect_factory(): void
    {
        $r = Response::redirect('/login');

        self::assertSame(302, $r->statusCode());
        self::assertSame('/login', $r->header('Location'));
    }

    #[Test]
    public function static_redirect_with_custom_status(): void
    {
        $r = Response::redirect('/gone', 301);

        self::assertSame(301, $r->statusCode());
    }

    #[Test]
    public function static_empty_factory(): void
    {
        $r = Response::empty();

        self::assertSame(204, $r->statusCode());
        self::assertSame('', $r->body());
    }

    #[Test]
    public function isSent_returns_false_initially(): void
    {
        $r = new Response();

        self::assertFalse($r->isSent());
    }

    #[Test]
    public function send_marks_as_sent(): void
    {
        $r = new Response('output');

        ob_start();
        $r->send();
        $output = ob_get_clean();

        self::assertTrue($r->isSent());
        self::assertSame('output', $output);
    }

    #[Test]
    public function send_is_idempotent(): void
    {
        $r = new Response('data');

        ob_start();
        $r->send();
        $first = ob_get_clean();

        ob_start();
        $r->send();
        $second = ob_get_clean();

        self::assertSame('data', $first);
        self::assertSame('', $second, 'Should not output again on second send');
    }

    #[Test]
    public function reset_restores_default_state(): void
    {
        $r = new Response('body', 404, ['X-Custom' => 'val']);
        $r->reset();

        self::assertSame(200, $r->statusCode());
        self::assertSame('', $r->body());
        self::assertNull($r->header('X-Custom'));
        self::assertFalse($r->isSent());
    }

    #[Test]
    public function reset_is_fluent(): void
    {
        $r = new Response();
        $ret = $r->reset();

        self::assertSame($r, $ret);
    }

    #[Test]
    public function chaining_returns_self(): void
    {
        $r = new Response();
        $r->withStatus(201)
            ->withHeader('X-Foo', 'bar')
            ->withBody('ok');

        self::assertSame(201, $r->statusCode());
        self::assertSame('bar', $r->header('X-Foo'));
        self::assertSame('ok', $r->body());
    }
}
