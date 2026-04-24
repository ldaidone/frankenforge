<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\View\Responder;

/**
 * Toggles a feature flag and returns the updated toggle list.
 */
final class ToggleFeature
{
    private const string COMPONENT = __DIR__ . '/../Views/Components/toggle-list.html.php';

    public function __construct(
        private readonly Responder $responder,
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public function __invoke(Request $request, Response $response, array $params): Response
    {
        $featureId = $params['feature'] ?? '';

        $toggles = $this->toggles();
        foreach ($toggles as &$t) {
            if ($t['id'] === $featureId) {
                $t['enabled'] = !$t['enabled'];
                break;
            }
        }
        unset($t);

        return $this->responder->respond(
            viewPath: self::COMPONENT,
            layoutPath: null,
            data: [
                'toggles' => $toggles,
                'toggleUrl' => '/dashboard/toggle/{id}',
            ],
            json: fn() => $toggles,
        );
    }

    /**
     * @return list<array{id:string, label:string, enabled:bool}>
     */
    private function toggles(): array
    {
        return [
            ['id' => 'dark-mode',      'label' => 'Dark Mode',      'enabled' => true],
            ['id' => 'notifications',  'label' => 'Notifications',  'enabled' => false],
            ['id' => 'analytics',      'label' => 'Analytics',      'enabled' => false],
        ];
    }
}
