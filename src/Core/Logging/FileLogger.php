<?php

declare(strict_types=1);

namespace FrankenForge\Core\Logging;

/**
 * Lightweight file logger for audit trails.
 *
 * Writes JSON lines to a log file (one JSON object per line for easy parsing).
 */
final class FileLogger
{
    private const array LEVELS = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
    ];

    private const string DEFAULT_LEVEL = 'info';

    private \DateTimeImmutable $start;

    public function __construct(
        private readonly string $path,
        private readonly string $level = self::DEFAULT_LEVEL,
    ) {
        $this->start = new \DateTimeImmutable();
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        if (!isset(self::LEVELS[$level])) {
            $level = self::DEFAULT_LEVEL;
        }

        if (self::LEVELS[$level] < self::LEVELS[$this->level]) {
            return;
        }

        $entry = [
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'elapsed' => (new \DateTimeImmutable())->diff($this->start)->f * 1000,
        ];

        file_put_contents(
            $this->path,
            json_encode($entry, JSON_THROW_ON_ERROR) . "\n",
            FILE_APPEND | LOCK_EX,
        );
    }
}