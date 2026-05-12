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
use FrankenForge\Core\Validation\Validator;
use FrankenForge\Domains\Admin\Repositories\AdminUserRepositoryInterface;
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Updates the user profile (name/email).
 */
final class PostProfile
{
    public function __construct(
        private readonly Auth $auth,
        private readonly AdminUserRepositoryInterface $users,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $user = $this->auth->user();

        if ($user === null) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        $data = $request->body();
        $v = Validator::make($data, [
            'name' => ['required', 'min:2', 'max:100'],
            'email' => ['required', 'email'],
        ]);

        if ($v->fails()) {
            $this->flash->set('profile_error', $v->errors()['name'] ?? $v->errors()['email'] ?? 'Validation failed');
            return $response->withStatus(302)->withHeader('Location', '/dashboard/profile');
        }

        $name = trim($data['name']);
        $email = trim($data['email']);

        $this->users->save(new \FrankenForge\Domains\Admin\Entities\AdminUser(
            id: $user->id,
            name: $name,
            email: $email,
            passwordHash: $user->passwordHash,
            mustChangePassword: $user->mustChangePassword,
            createdAt: $user->createdAt,
        ));

        $this->flash->set('profile_success', 'Profile updated.');

        return $response->withStatus(302)->withHeader('Location', '/dashboard/profile');
    }
}
