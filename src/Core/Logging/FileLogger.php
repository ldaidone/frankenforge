<?php
/**
 * FrankenForge — FrankenForge\Core\Logging
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
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

    /**
     * @param string $path Path to the log file.
     * @param string $level Minimum log level (debug, info, warning, error).
     */
    public function __construct(
        private readonly string $path,
        private readonly string $level = self::DEFAULT_LEVEL,
    ) {
        $this->start = new \DateTimeImmutable();
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws \JsonException
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws \JsonException
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws \JsonException
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws \JsonException
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Internal log method that handles level filtering and writing to the file.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     * @throws \JsonException
     */
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
