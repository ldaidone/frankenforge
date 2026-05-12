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

/**
 * Centralized error handling for HTTP exceptions.
 *
 * Provides methods to generate appropriate HTTP responses for common error scenarios:
 * - 404 Not Found
 * - 405 Method Not Allowed
 * - 500 Internal Server Error
 *
 * Each method renders a user-friendly error page using the provided View component,
 * and includes relevant information such as the error code, title, message, and a back URL.
 */
final readonly class ErrorHandler
{
    /**
     * @param View $view The view renderer for generating error pages.
     * @param string $viewsPath The base path to the application's view templates.
     */
    public function __construct(
        private View $view,
        private string $viewsPath,
    ) {}

    /**
     * Handle a 404 Not Found error.
     *
     * @param Request $request The incoming HTTP request, used to determine the referer for the back URL.
     * @return Response A response with a 404 status code and a rendered error page.
     */
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

    /**
     * Handle a 405 Method Not Allowed error.
     *
     * @param array $allowedMethods The HTTP methods that are allowed for the requested resource.
     * @return Response A response with a 405 status code, an Allow header, and a rendered error page.
     */
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

    /**
     * Handle a 500 Internal Server Error.
     *
     * @param \Throwable $e The exception that caused the server error, used to provide debug information if APP_DEBUG is enabled.
     * @return Response A response with a 500 status code and a rendered error page.
     */
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

    /**
     * Render an error page using the provided code, title, message, and back URL.
     *
     * Attempts to use a custom error template if available, otherwise falls back to a basic built-in template.
     *
     * @param int $code The HTTP status code for the error.
     * @param string $title A short title describing the error.
     * @param string $message A detailed message explaining the error.
     * @param string $backUrl A URL for the user to navigate back to a safe location.
     * @return string The rendered HTML content of the error page.
     */
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
