<?php
/**
 * FrankenForge — FrankenForge\Core\Http
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Core\Http;

/**
 * Lightweight HTTP Response abstraction.
 *
 * Holds status code, headers, and body. Provides methods to modify
 * and send the response. Deliberately simple — no PSR-7 bloat.
 */
final class Response
{
    private int $statusCode;

    /** @var array<string, string|int> */
    private array $headers;

    private string $body;

    private bool $sent = false;

    /**
     * Create a new response with optional body, status code, and headers.
     */
    public function __construct(
        string $body = '',
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Create a new response with the given status code.
     *
     * @param string $body
     * @param int $statusCode
     * @param array $headers
     * @return self
     */
    public static function make(string $body = '', int $statusCode = 200, array $headers = []): self
    {
        return new self($body, $statusCode, $headers);
    }

    /**
     * Create a JSON response.
     *
     * @param mixed $data
     * @param int $statusCode
     * @return self
     * @throws \JsonException
     */
    public static function json(mixed $data, int $statusCode = 200): self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create an HTML response.
     *
     * @param string $html
     * @param int $statusCode
     * @return self
     */
    public static function html(string $html, int $statusCode = 200): self
    {
        return new self(
            $html,
            $statusCode,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * Create a redirect response.
     *
     * @param string $url
     * @param int $statusCode
     * @return self
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    /**
     * Create an empty response.
     *
     * @param int $statusCode
     * @return self
     */
    public static function empty(int $statusCode = 204): self
    {
        return new self('', $statusCode);
    }

    /**
     * Get the current status code.
     *
     * @return int
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set the status code (fluent, mutable).
     *
     * @param int $statusCode
     * @return $this
     */
    public function withStatus(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get all headers or a single header by name (case-insensitive).
     *
     * @param string|null $name
     * @return ($name is null ? array<string, string|int> : string|int|null)
     */
    public function header(?string $name = null): mixed
    {
        if ($name === null) {
            return $this->headers;
        }

        $lowerName = strtolower($name);

        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $lowerName) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Set a header (fluent, mutable).
     *
     * @param string $name
     * @param string|int $value
     * @return $this
     */
    public function withHeader(string $name, string|int $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Remove a header (fluent, mutable).
     *
     * @param string $name
     * @return $this
     */
    public function withoutHeader(string $name): self
    {
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === strtolower($name)) {
                unset($this->headers[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Get the body.
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Set the body (fluent, mutable).
     *
     * @param string $body
     * @return $this
     */
    public function withBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Check if the response has already been sent.
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Reset the response to initial state. Call at the start of each worker request.
     */
    public function reset(): self
    {
        $this->statusCode = 200;
        $this->headers = [];
        $this->body = '';
        $this->sent = false;

        return $this;
    }

    /**
     * Send the response to the client.
     */
    public function send(): void
    {
        if ($this->sent) {
            return;
        }

        if (!headers_sent()) {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->body;

        $this->sent = true;
    }
}
