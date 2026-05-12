<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Domains\Dashboard\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\Session\FlashMessages;

/**
 * Triggers a flash message and redirects back.
 */
final class TriggerFlash
{
    public function __construct(
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response, array $params): Response
    {
        $type = $params['type'] ?? 'info';
        $message = match ($type) {
            'success' => 'Operation completed successfully!',
            'error' => 'Something went wrong. Please try again.',
            'warning' => 'This action requires attention.',
            default => 'This is an informational message.',
        };

        $this->flash->$type($message);

        return $response
            ->withStatus(302)
            ->withHeader('Location', '/dashboard');
    }
}
