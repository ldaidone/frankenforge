<?php

declare(strict_types=1);

namespace FrankenForge\Core\View;

/**
 * Native PHP View Engine.
 *
 * Uses PHP itself as the templating language via output buffering.
 * No parser, no DSL — just .html.php files with inline PHP.
 *
 * Supports two rendering modes:
 *   - Full layout: wraps a view inside a base layout shell
 *   - Fragment: renders only the view (for HTMX partial updates)
 */
final class View
{
    /**
     * @param string $viewPath Absolute path to a .html.php template file
     * @param array<string, mixed> $data Variables available inside the template
     * @return string Rendered output
     */
    public function render(string $viewPath, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $output = ob_get_clean();

        if ($output === '') {
            $output = "<!-- View rendered empty: {$viewPath} -->";
        }

        return $output;
    }

    /**
     * Render a view wrapped inside a layout.
     *
     * @param string $layoutPath Absolute path to a layout .html.php file
     * @param string $viewPath Absolute path to the view .html.php file
     * @param array<string, mixed> $data Variables available inside both templates
     * @return string Rendered output (layout wrapping view)
     */
    public function layout(string $layoutPath, string $viewPath, array $data = []): string
    {
        // 1. Render the inner view, capture output
        $content = $this->render($viewPath, $data);

        // 2. Pass rendered content + data into the layout
        return $this->render($layoutPath, [...$data, 'content' => $content]);
    }
}
