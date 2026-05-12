<?php
/**
 * FrankenForge — FrankenForge\Core\Http\Middleware
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Core\Http\Middleware;

use FrankenForge\Core\Http\MiddlewareInterface;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Security\CsrfToken;

/**
 * CSRF protection middleware.
 *
 * Validates CSRF token on state-changing methods (POST, PUT, PATCH, DELETE).
 * GET, HEAD, OPTIONS requests are always allowed.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const array SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private readonly CsrfToken $csrf,
    ) {}

    public function process(Request $request, Response $response, callable $next): Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next($request, $response);
        }

        $token = $request->body('_csrf')
            ?? $request->json()['_csrf'] ?? null
            ?? $request->header('X-CSRF-Token')
            ?? $request->header('X-XSRF-Token');

        if (!$this->csrf->validate($token)) {
            return $response
                ->withStatus(403)
                ->withHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody('<html><body><h1>403 Forbidden</h1><p>CSRF token validation failed.</p></body></html>');
        }

        return $next($request, $response);
    }
}
