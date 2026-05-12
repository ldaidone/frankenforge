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
 * Toggles a feature flag in the database and returns the updated list.
 */
final class ToggleFeature
{
    private const string COMPONENT = __DIR__ . '/../Views/Components/toggle-list.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly ToggleRepositoryInterface $toggles,
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public function __invoke(Request $request, Response $response, array $params): Response
    {
        $id = $params['feature'] ?? '';
        $result = $this->toggles->toggle($id);

        if (!$result['success']) {
            return $this->responder->respond(
                viewPath: self::COMPONENT,
                layoutPath: null,
                data: ['toggles' => $this->toggles->findAll(), 'toggleUrl' => '/dashboard/toggle/{id}'],
                json: fn() => $this->toggles->findAll(),
            );
        }

        return $this->responder->respond(
            viewPath: self::COMPONENT,
            layoutPath: null,
            data: [
                'toggles' => $this->toggles->findAll(),
                'toggleUrl' => '/dashboard/toggle/{id}',
            ],
            json: fn() => $this->toggles->findAll(),
        );
    }
}
