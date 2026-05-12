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
use FrankenForge\Domains\Admin\Services\Auth;

/**
 * Saves changes to the .env file.
 */
final class PostEnvSave
{
    public function __construct(
        private readonly Auth $auth,
        private readonly FlashMessages $flash,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $user = $this->auth->user();
        if ($user === null || $user->mustChangePassword) {
            return $response->withStatus(302)->withHeader('Location', '/dashboard/login');
        }

        $raw = $request->body('env_content') ?? '';
        $envPath = __DIR__ . '/../../../../.env';

        if (!is_writable($envPath) && file_exists($envPath)) {
            $this->flash->set('env_error', '.env file is not writable. Check file permissions.');
            return $response->withStatus(302)->withHeader('Location', '/dashboard/env');
        }

        file_put_contents($envPath, $raw);
        $this->flash->set('env_success', '.env file saved.');

        return $response->withStatus(302)->withHeader('Location', '/dashboard/env');
    }
}
