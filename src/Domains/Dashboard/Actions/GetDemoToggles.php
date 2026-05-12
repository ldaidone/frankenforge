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
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Dashboard\Repositories\ToggleRepositoryInterface;

/**
 * Returns the toggle list as an HTML fragment for the demo page.
 */
final class GetDemoToggles
{
    private const string COMPONENT = __DIR__ . '/../Views/Components/toggle-list.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly ToggleRepositoryInterface $toggles,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $items = $this->toggles->findAll();

        return $this->responder->respond(
            viewPath: self::COMPONENT,
            layoutPath: null,
            data: [
                'toggles' => $items,
                'toggleUrl' => '/dashboard/toggle/{id}',
            ],
            json: fn() => $items,
        );
    }
}
