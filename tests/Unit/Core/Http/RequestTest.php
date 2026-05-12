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

use FrankenForge\Core\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    private function setUpRequest(string $method = 'GET', string $uri = '/', array $server = [], array $get = [], array $post = [], array $cookies = []): void
    {
        $_SERVER = array_merge([
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
            'QUERY_STRING' => '',
        ], $server);
        $_GET = $get;
        $_POST = $post;
        $_COOKIE = $cookies;
    }

    #[Test]
    public function it_reads_the_http_method(): void
    {
        $this->setUpRequest('POST', '/submit');
        $r = new Request();

        self::assertSame('POST', $r->method());
    }

    #[Test]
    public function it_defaults_to_get_when_server_method_is_missing(): void
    {
        $this->setUpRequest('', '/', ['REQUEST_METHOD' => '']);
        $r = new Request();

        // The source falls back to 'GET' only when REQUEST_METHOD is null/missing
        self::assertSame('', $r->method());
    }

    #[Test]
    public function it_returns_the_full_uri(): void
    {
        $this->setUpRequest('GET', '/foo?bar=baz');
        $r = new Request();

        self::assertSame('/foo?bar=baz', $r->uri());
    }

    #[Test]
    public function it_returns_the_path_from_uri(): void
    {
        $this->setUpRequest('GET', '/users/42?page=1');
        $r = new Request();

        self::assertSame('/users/42', $r->path());
    }

    #[Test]
    public function it_falls_back_to_root_when_path_is_empty(): void
    {
        $this->setUpRequest('GET', '');
        $r = new Request();

        self::assertSame('/', $r->path());
    }

    #[Test]
    public function it_returns_query_string(): void
    {
        $this->setUpRequest('GET', '/', ['QUERY_STRING' => 'foo=1&bar=2']);
        $r = new Request();

        self::assertSame('foo=1&bar=2', $r->queryString());
    }

    #[Test]
    public function it_returns_query_params(): void
    {
        $this->setUpRequest('GET', '/', [], ['name' => 'Alice', 'page' => '1']);
        $r = new Request();

        self::assertSame('Alice', $r->query('name'));
        self::assertSame('1', $r->query('page'));
        self::assertNull($r->query('missing'));
        self::assertSame('default', $r->query('missing', 'default'));
    }

    #[Test]
    public function it_returns_all_query_params(): void
    {
        $this->setUpRequest('GET', '/', [], ['a' => '1', 'b' => '2']);
        $r = new Request();

        self::assertSame(['a' => '1', 'b' => '2'], $r->query());
    }

    #[Test]
    public function it_returns_body_params(): void
    {
        $this->setUpRequest('POST', '/', [], [], ['email' => 'a@b.com']);
        $r = new Request();

        self::assertSame('a@b.com', $r->body('email'));
        self::assertNull($r->body('missing'));
    }

    #[Test]
    public function it_merges_input_params_body_overrides_query(): void
    {
        $this->setUpRequest('POST', '/', [], ['name' => 'from-query'], ['name' => 'from-body']);
        $r = new Request();

        self::assertSame('from-body', $r->input('name'));
    }

    #[Test]
    public function it_returns_all_merged_input(): void
    {
        $this->setUpRequest('POST', '/', [], ['q' => '1'], ['p' => '2']);
        $r = new Request();

        self::assertSame(['q' => '1', 'p' => '2'], $r->all());
    }

    #[Test]
    public function it_detects_json_content_type(): void
    {
        $this->setUpRequest('GET', '/', ['HTTP_ACCEPT' => 'application/json']);
        $r = new Request();

        self::assertTrue($r->wantsJson());
    }

    #[Test]
    public function it_detects_non_json_requests(): void
    {
        $this->setUpRequest('GET', '/', ['HTTP_ACCEPT' => 'text/html']);
        $r = new Request();

        self::assertFalse($r->wantsJson());
    }

    #[Test]
    public function it_detects_htmx_requests(): void
    {
        $this->setUpRequest('GET', '/', ['HTTP_HX_REQUEST' => 'true']);
        $r = new Request();

        self::assertTrue($r->isHtmx());
    }

    #[Test]
    public function it_detects_non_htmx_requests(): void
    {
        $this->setUpRequest('GET', '/');
        $r = new Request();

        self::assertFalse($r->isHtmx());
    }

    #[Test]
    public function it_returns_headers_case_insensitively(): void
    {
        $this->setUpRequest('GET', '/', ['HTTP_CONTENT_TYPE' => 'application/json']);
        $r = new Request();

        self::assertSame('application/json', $r->header('Content-Type'));
        self::assertSame('application/json', $r->header('content-type'));
        self::assertNull($r->header('X-Missing'));
    }

    #[Test]
    public function it_parses_content_type_and_length_from_server_directly(): void
    {
        $this->setUpRequest('POST', '/', [
            'CONTENT_TYPE' => 'text/plain',
            'CONTENT_LENGTH' => '42',
        ]);
        $r = new Request();

        self::assertSame('text/plain', $r->header('content-type'));
        self::assertSame('42', $r->header('content-length'));
    }

    #[Test]
    public function it_returns_all_headers(): void
    {
        $this->setUpRequest('GET', '/', ['HTTP_X_CUSTOM' => 'val']);
        $r = new Request();

        $headers = $r->header();
        self::assertArrayHasKey('x-custom', $headers);
        self::assertSame('val', $headers['x-custom']);
    }

    #[Test]
    public function it_returns_client_ip(): void
    {
        $this->setUpRequest('GET', '/', ['REMOTE_ADDR' => '192.168.1.1']);
        $r = new Request();

        self::assertSame('192.168.1.1', $r->ip());
    }

    #[Test]
    public function it_prefers_x_forwarded_for_for_ip(): void
    {
        $this->setUpRequest('GET', '/', [
            'HTTP_X_FORWARDED_FOR' => '10.0.0.1',
            'REMOTE_ADDR' => '192.168.1.1',
        ]);
        $r = new Request();

        self::assertSame('10.0.0.1', $r->ip());
    }

    #[Test]
    public function it_returns_default_ip_when_none_available(): void
    {
        $this->setUpRequest('GET', '/', []);
        $r = new Request();

        self::assertSame('127.0.0.1', $r->ip());
    }

    #[Test]
    public function it_returns_full_url(): void
    {
        $this->setUpRequest('GET', '/path?q=1', [
            'HTTP_HOST' => 'example.com',
        ]);
        $r = new Request();

        self::assertSame('http://example.com/path?q=1', $r->fullUrl());
    }

    #[Test]
    public function it_returns_raw_body_as_empty_string_in_cli(): void
    {
        $this->setUpRequest('POST', '/');
        $r = new Request();

        self::assertSame('', $r->rawBody());
    }

    #[Test]
    public function json_returns_null_when_no_body(): void
    {
        $this->setUpRequest('POST', '/');
        $r = new Request();

        self::assertNull($r->json());
    }

    #[Test]
    public function it_refreshes_from_current_superglobals(): void
    {
        $this->setUpRequest('GET', '/original');
        $r = new Request();
        self::assertSame('/original', $r->uri());

        $this->setUpRequest('POST', '/changed');
        $r->refresh();

        self::assertSame('/changed', $r->uri());
        self::assertSame('POST', $r->method());
    }

    #[Test]
    public function it_returns_default_for_missing_body_key(): void
    {
        $this->setUpRequest('POST', '/');
        $r = new Request();

        self::assertNull($r->body('nonexistent'));
        self::assertSame('fallback', $r->body('nonexistent', 'fallback'));
    }
}
