<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Admin\Services;

use FrankenForge\Core\Logging\FileLogger;
use FrankenForge\Domains\Admin\Entities\AdminUser;
use FrankenForge\Domains\Admin\Repositories\AdminUserRepositoryInterface;

/**
 * Session-based authentication service.
 *
 * Uses native PHP sessions. No abstraction layer — sessions are fast enough.
 */
final class Auth
{
    private const string SESSION_KEY = '_frankenforge_auth_user_id';
    private $logger;

    public function __construct(
        private readonly AdminUserRepositoryInterface $users,
    ) {
        $this->logger = new FileLogger(__DIR__ . '/../../../../storage/app.log');
    }

    public function login(string $email, string $password): ?AdminUser
    {
        $this->logger->info('Login attempt', ['email' => $email, 'timestamp' => (new \DateTimeImmutable())->format('c')]);
        $user = $this->users->findByEmail($email);
        $this->logger->info('Login attempt::After', ['user' => $user, 'timestamp' => (new \DateTimeImmutable())->format('c')]);
        if ($user === null) {
            return null;
        }

        if (!$this->users->verifyPassword($password, $user)) {
            return null;
        }

        $this->setUser($user->id);

        return $user;
    }

    public function logout(): void
    {
        $_SESSION[self::SESSION_KEY] = null;
        unset($_SESSION[self::SESSION_KEY]);
        session_destroy();
        // Note: in FrankenPHP worker mode, session_destroy() clears the server-side session
        // but the session cookie persists on the client; session_start() on the next request
        // will create a fresh session. This is acceptable for logout purposes.
    }

    public function user(): ?AdminUser
    {
        $id = $_SESSION[self::SESSION_KEY] ?? null;

        if ($id === null) {
            return null;
        }

        return $this->users->findById($id);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function mustChangePassword(): bool
    {
        $user = $this->user();
        return $user !== null && $user->mustChangePassword;
    }

    private function setUser(string $id): void
    {
        $_SESSION[self::SESSION_KEY] = $id;
    }
}
