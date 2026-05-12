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

use Random\RandomException;

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

    /**
     * Generate a new CSRF token and store it in the session.
     *
     * @return string The generated CSRF token.
     * @throws RandomException
     */
    public function generate(): string
    {
        $this->token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::SESSION_KEY] = $this->token;
        return $this->token;
    }

    /**
     * Get the current CSRF token, generating one if it doesn't exist.
     *
     * @return string The current CSRF token.
     * @throws RandomException
     */
    public function getToken(): string
    {
        if ($this->token === null) {
            $this->token = $_SESSION[self::SESSION_KEY] ?? $this->generate();
        }
        return $this->token;
    }

    /**
     * Get an HTML hidden input field with the CSRF token.
     *
     * @param string $name The name attribute for the hidden input (default: '_csrf').
     * @return string The HTML string for the hidden input field.
     * @throws RandomException
     */
    public function getHiddenField(string $name = '_csrf'): string
    {
        return '<input type="hidden" name="' . $name . '" value="' . $this->getToken() . '">';
    }

    /**
     * Validate a given CSRF token against the stored token.
     *
     * @param string|null $token The token to validate (can be null or empty).
     * @param string $name The name of the token field (default: '_csrf').
     * @return bool True if the token is valid, false otherwise.
     * @throws RandomException
     */
    public function validate(?string $token, string $name = '_csrf'): bool
    {
        if ($token === null || $token === '') {
            // In strict mode, missing token always fails; in non-strict mode it passes (e.g. for API clients)
            return !$this->strict;
        }

        return hash_equals($this->getToken(), $token);
    }
}
