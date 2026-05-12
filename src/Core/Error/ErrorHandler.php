<?php
/**
 * FrankenForge — FrankenForge\Core\Error
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */

declare(strict_types=1);

namespace FrankenForge\Core\Error;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\View\View;

final readonly class ErrorHandler
{
    public function __construct(
        private View $view,
        private string $viewsPath,
    ) {}

    public function notFound(Request $request): Response
    {
        $body = $this->renderError(
            code: 404,
            title: 'Page Not Found',
            message: "The page you requested could not be found.",
            backUrl: $request->header('Referer') ?? '/',
        );

        return (new Response())
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($body);
    }

    public function methodNotAllowed(array $allowedMethods): Response
    {
        $body = $this->renderError(
            code: 405,
            title: 'Method Not Allowed',
            message: "The HTTP method used is not allowed for this resource. Allowed: " . implode(', ', $allowedMethods),
            backUrl: '/',
        );

        return (new Response())
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $allowedMethods))
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($body);
    }

    public function serverError(\Throwable $e): Response
    {
        $message = $_ENV['APP_DEBUG'] ?? false
            ? (string) $e
            : 'An internal server error occurred. Please try again later.';

        $body = $this->renderError(
            code: 500,
            title: 'Internal Server Error',
            message: $message,
            backUrl: '/',
        );

        return (new Response())
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($body);
    }

    private function renderError(int $code, string $title, string $message, string $backUrl): string
    {
        $templatePath = $this->viewsPath . '/error.html.php';

        if (file_exists($templatePath)) {
            return $this->view->render($templatePath, [
                'code' => $code,
                'title' => $title,
                'message' => $message,
                'backUrl' => $backUrl,
            ]);
        }

        return $this->view->render(__DIR__ . '/error-basic.html.php', [
            'code' => $code,
            'title' => $title,
            'message' => $message,
            'backUrl' => $backUrl,
        ]);
    }
}
