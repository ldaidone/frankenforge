<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Admin\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Logs out the current user.
 */
final class PostLogout
{
    public function __construct(
        private readonly Auth $auth,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $this->auth->logout();

        return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
    }
}
