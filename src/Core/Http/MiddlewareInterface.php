<?php

declare(strict_types=1);

namespace FrankenForge\Core\Http;

/**
 * PSR-15 style middleware interface.
 *
 * Middleware wraps the request/response cycle, allowing cross-cutting
 * concerns (auth, logging, CORS) to run before and after the handler.
 */
interface MiddlewareInterface
{
    public function process(Request $request, Response $response, callable $next): Response;
}