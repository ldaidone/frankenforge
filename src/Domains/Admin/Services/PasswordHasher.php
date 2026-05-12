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

/**
 * Thin wrapper around PHP's native password_hash / password_verify.
 */
final class PasswordHasher
{
    /**
     * Hash a plain-text password.
     */
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify a plain-text password against a hash.
     */
    public function verify(string $password, string $hash): bool
    {
        $logger = new FileLogger(__DIR__ . '/../../../../storage/app.log');
        $o = password_verify($password, $hash);
        $logger->info('Password verification', ['password' => $password, 'hash' => $hash, 'result' => $o, 'timestamp' => (new \DateTimeImmutable())->format('c')]);
        return $o;
    }

    /**
     * Check if a hash needs rehashing (e.g. algorithm upgrade).
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
