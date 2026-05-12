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
use FrankenForge\Core\Session\FlashMessages;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Shows the login page.
 */
final class GetLogin
{
    private const string VIEW = __DIR__ . '/../Views/login.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response, array $params = []): Response
    {
        if ($this->auth->check()) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/overview');
        }

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: __DIR__ . '/../../../../templates/layout.html.php',
            data: [
                'title' => 'FrankenForge • Login',
                'email' => $this->flash->pull('login_email') ?? '',
                'error' => $this->flash->pull('login_error'),
            ],
        );
    }
}
