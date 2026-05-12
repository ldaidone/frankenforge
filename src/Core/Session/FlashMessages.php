<?php
/**
 * FrankenForge — FrankenForge\Core\Session
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Core\Session;

/**
 * Lightweight flash message manager.
 *
 * Stores messages in session, auto-clears after display.
 */
final class FlashMessages
{
    private const string KEY = '_flash';

    public function __construct() {}

    /**
     * @param string $message
     * @return void
     */
    public function success(string $message): void
    {
        $this->add('success', $message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function error(string $message): void
    {
        $this->add('error', $message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function warning(string $message): void
    {
        $this->add('warning', $message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function info(string $message): void
    {
        $this->add('info', $message);
    }

    /**
     * @param string $type
     * @param string $message
     * @return void
     */
    private function add(string $type, string $message): void
    {
        $_SESSION[self::KEY][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function all(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $messages;
    }

    public function has(): bool
    {
        return !empty($_SESSION[self::KEY]);
    }

    public function clear(): void
    {
        unset($_SESSION[self::KEY]);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[self::KEY . '_' . $key] = $value;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $sessionKey = self::KEY . '_' . $key;
        $value = $_SESSION[$sessionKey] ?? $default;
        unset($_SESSION[$sessionKey]);
        return $value;
    }
}
