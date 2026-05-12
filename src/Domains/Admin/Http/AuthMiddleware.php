<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Admin\Http;

use FrankenForge\Core\Http\MiddlewareInterface;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Protects routes: redirects unauthenticated users to the login page.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    private const array PUBLIC_PATHS = [
        '/dashboard/login',
        '/dashboard/toggle/',
    ];

    public function __construct(
        private readonly Auth $auth,
    ) {}

    public function process(Request $request, Response $response, callable $next): Response
    {
        $path = $request->path();

        if (!str_starts_with($path, '/dashboard')) {
            return $next($request, $response);
        }

        foreach (self::PUBLIC_PATHS as $public) {
            if (str_starts_with($path, $public)) {
                return $next($request, $response);
            }
        }

        if ($this->auth->check()) {
            return $next($request, $response);
        }

        return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
    }
}
