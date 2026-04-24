<?php

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

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function success(string $message): void
    {
        $this->add('success', $message);
    }

    public function error(string $message): void
    {
        $this->add('error', $message);
    }

    public function warning(string $message): void
    {
        $this->add('warning', $message);
    }

    public function info(string $message): void
    {
        $this->add('info', $message);
    }

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
}