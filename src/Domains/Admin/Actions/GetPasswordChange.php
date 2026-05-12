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
 * Shows the forced password change page.
 */
final class GetPasswordChange
{
    private const string VIEW = __DIR__ . '/../Views/password-change.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response, array $params = []): Response
    {
        $user = $this->auth->user();

        if ($user === null) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: null,
            data: [
                'title' => 'FrankenForge • Change Password',
                'user' => $user,
                'forced' => $user->mustChangePassword,
                'error' => $this->flash->pull('password_error'),
            ],
        );
    }
}
