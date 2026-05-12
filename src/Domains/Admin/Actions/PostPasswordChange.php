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
use FrankenForge\Domains\Admin\Services\PasswordHasher;

/**
 * Processes password change.
 */
final class PostPasswordChange
{
    public function __construct(
        private readonly Auth $auth,
        private readonly PasswordHasher $hasher,
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

        $rules = [
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8'],
            'confirm_password' => ['required', 'matches:new_password'],
        ];

        $v = Validator::make($data, $rules);

        if ($v->fails()) {
            $this->flash->set('password_error', reset($v->errors()));
            return $response->withStatus(302)->withHeader('Location', '/dashboard/password');
        }

        if (!$this->hasher->verify($data['current_password'], $user->passwordHash)) {
            $this->flash->set('password_error', 'Current password is incorrect.');
            return $response->withStatus(302)->withHeader('Location', '/dashboard/password');
        }

        $hash = $this->hasher->hash($data['new_password']);
        $this->users->updatePassword($user->id, $hash);

        if ($user->mustChangePassword) {
            $this->users->clearMustChangePassword($user->id);
        }

        $this->flash->set('profile_success', 'Password changed successfully.');

        return $response->withStatus(302)->withHeader('Location', '/dashboard/overview');
    }
}
