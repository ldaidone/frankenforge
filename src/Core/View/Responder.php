<?php

declare(strict_types=1);

namespace FrankenForge\Core\View;

use Closure;
use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;

/**
 * Content negotiation responder.
 *
 * Decides how to format the response based on the request:
 *   - Accept: application/json  → JSON response
 *   - HX-Request: true          → HTML fragment (view only, no layout)
 *   - Normal browser request    → Full HTML page (layout wraps view)
 *
 * A single action returns one Response; the Responder picks the representation.
 */
final class Responder
{
    public function __construct(
        private readonly \Closure $makeRequest,
        private readonly Response $response,
        private readonly View $view,
    ) {}

    /**
     * Negotiate the response format and render.
     *
     * @param string $viewPath Absolute path to a .html.php view file
     * @param string|null $layoutPath Absolute path to a layout .html.php file.
     *                                If null and not HTMX request, the view is rendered standalone.
     * @param array<string, mixed> $data Variables passed to the view
     * @param Closure|null $json If provided, this closure returns JSON-serializable data
     *                          for API/JSON requests. If null and client wants JSON,
     *                          falls back to HTML rendering.
     */
    public function respond(
        string $viewPath,
        ?string $layoutPath = null,
        array $data = [],
        ?Closure $json = null,
    ): Response {
        $request = ($this->makeRequest)();

        if ($request->wantsJson() && $json !== null) {
            return $this->response
                ->withBody(json_encode($json(), JSON_THROW_ON_ERROR))
                ->withHeader('Content-Type', 'application/json');
        }

        if ($request->isHtmx()) {
            return $this->response
                ->withBody($this->view->render($viewPath, $data))
                ->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        if ($layoutPath !== null) {
            return $this->response
                ->withBody($this->view->layout($layoutPath, $viewPath, $data))
                ->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        return $this->response
            ->withBody($this->view->render($viewPath, $data))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Convenience: respond with JSON only.
     *
     * @param mixed $data JSON-serializable data
     */
    public function json(mixed $data): Response
    {
        return $this->response
            ->withBody(json_encode($data, JSON_THROW_ON_ERROR))
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Convenience: respond with HTML fragment only (no layout, ever).
     */
    public function fragment(string $viewPath, array $data = []): Response
    {
        return $this->response
            ->withBody($this->view->render($viewPath, $data))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Convenience: respond with full HTML page (layout wraps view, always).
     */
    public function page(string $layoutPath, string $viewPath, array $data = []): Response
    {
        return $this->response
            ->withBody($this->view->layout($layoutPath, $viewPath, $data))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
