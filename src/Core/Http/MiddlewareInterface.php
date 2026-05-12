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
 * PSR-15 style middleware interface.
 *
 * Middleware wraps the request/response cycle, allowing cross-cutting
 * concerns (auth, logging, CORS) to run before and after the handler.
 */
interface MiddlewareInterface
{
    public function process(Request $request, Response $response, callable $next): Response;
}
