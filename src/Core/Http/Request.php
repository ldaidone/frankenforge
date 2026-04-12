<?php

declare(strict_types=1);

namespace FrankenForge\Core\Http;

/**
 * Lightweight HTTP Request abstraction.
 *
 * Wraps superglobals ($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES)
 * and raw request body behind a clean, typed interface.
 */
final class Request
{
    private string $method;
    private string $uri;
    private string $queryString;

    /** @var array<string, mixed> */
    private array $headers;

    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $body;

    /** @var array<string, mixed> */
    private array $cookies;

    /** @var array<string, mixed> */
    private array $server;

    private string $rawBody;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->queryString = $_SERVER['QUERY_STRING'] ?? '';

        $this->query = $_GET;
        $this->body = $_POST;
        $this->cookies = $_COOKIE;
        $this->server = $_SERVER;

        $this->headers = $this->parseHeaders();

        $this->rawBody = file_get_contents('php://input') ?: '';
    }

    /**
     * Get the HTTP method (GET, POST, PUT, DELETE, etc.).
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get the full request URI (e.g., /users/42?foo=bar).
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get just the path portion of the URI (e.g., /users/42).
     */
    public function path(): string
    {
        return parse_url($this->uri, PHP_URL_PATH) ?: '/';
    }

    /**
     * Get the raw query string (e.g., foo=bar&baz=qux).
     */
    public function queryString(): string
    {
        return $this->queryString;
    }

    /**
     * Get all query parameters, or a single value by key.
     *
     * @template T
     * @param string|null $key
     * @param T $default
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Get all body parameters, or a single value by key.
     *
     * @template T
     * @param string|null $key
     * @param T $default
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function body(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }

        return $this->body[$key] ?? $default;
    }

    /**
     * Get a merged input map: body params override query params.
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Get a single input value (checks body first, then query).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all headers or a single header by name (case-insensitive).
     *
     * @param string|null $name
     * @param T $default
     * @return ($name is null ? array<string, mixed> : string|null)
     * @template T
     */
    public function header(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->headers;
        }

        $key = strtolower($name);

        return $this->headers[$key] ?? $default;
    }

    /**
     * Check if the request expects a JSON response.
     */
    public function wantsJson(): bool
    {
        $accept = $this->header('accept', '');

        return str_contains((string) $accept, 'application/json');
    }

    /**
     * Check if this is an HTMX request (HX-Request header present).
     */
    public function isHtmx(): bool
    {
        return $this->header('hx-request') !== null;
    }

    /**
     * Get the raw request body as a string.
     */
    public function rawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Get the raw body decoded as JSON.
     */
    public function json(): ?array
    {
        if ($this->rawBody === '') {
            return null;
        }

        $decoded = json_decode($this->rawBody, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get the full URL (including scheme and host).
     */
    public function fullUrl(): string
    {
        $scheme = $this->header('x-forwarded-proto') ?? ($this->server['HTTPS'] ?? 'off') === 'on' ? 'https' : 'http';
        $host = $this->header('host') ?? $this->server['HTTP_HOST'] ?? 'localhost';

        return "{$scheme}://{$host}{$this->uri}";
    }

    /**
     * Get the client IP address.
     */
    public function ip(): string
    {
        return $this->header('x-forwarded-for')
            ?? $this->header('x-real-ip')
            ?? $this->server['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    /**
     * Parse HTTP headers from $_SERVER.
     *
     * @return array<string, string>
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        // Content-Type and Content-Length don't have HTTP_ prefix
        if (isset($this->server['CONTENT_TYPE'])) {
            $headers['content-type'] = $this->server['CONTENT_TYPE'];
        }

        if (isset($this->server['CONTENT_LENGTH'])) {
            $headers['content-length'] = $this->server['CONTENT_LENGTH'];
        }

        return $headers;
    }
}
