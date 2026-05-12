<?php
/**
 * FrankenForge — FrankenForge\Core\Security
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Core\Security;

/**
 * Lightweight CSRF token manager.
 *
 * Generates and validates CSRF tokens for POST/PUT/PATCH/DELETE requests.
 */
final class CsrfToken
{
    private const string SESSION_KEY = '_csrf_token';
    private const int TOKEN_LENGTH = 32;

    private ?string $token = null;

    public function __construct(
        private readonly bool $strict = true,
    ) {}

    public function generate(): string
    {
        $this->token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::SESSION_KEY] = $this->token;
        return $this->token;
    }

    public function getToken(): string
    {
        if ($this->token === null) {
            $this->token = $_SESSION[self::SESSION_KEY] ?? $this->generate();
        }
        return $this->token;
    }

    public function getHiddenField(string $name = '_csrf'): string
    {
        return '<input type="hidden" name="' . $name . '" value="' . $this->getToken() . '">';
    }

    public function validate(?string $token, string $name = '_csrf'): bool
    {
        if ($token === null || $token === '') {
            return !$this->strict;
        }

        return hash_equals($this->getToken(), $token);
    }
}
