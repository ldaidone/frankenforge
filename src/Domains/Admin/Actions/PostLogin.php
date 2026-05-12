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
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Processes login attempt.
 */
final class PostLogin
{
    public function __construct(
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $email = trim($request->body('email') ?? '');
        $password = $request->body('password') ?? '';

        if ($email === '' || $password === '') {
            $this->flash->set('login_email', $email);
            $this->flash->set('login_error', 'Email and password are required.');

            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        $user = $this->auth->login($email, $password);

        if ($user === null) {
            $this->flash->set('login_email', $email);
            $this->flash->set('login_error', 'Invalid email or password.');

            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        if ($user->mustChangePassword) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/password');
        }

        return $response->withStatus(302)->withHeader('Location', '/dashboard/overview');
    }
}
