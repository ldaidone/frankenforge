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
use FrankenForge\Domains\Dashboard\Entities\Stat;
use FrankenForge\Domains\Dashboard\Repositories\StatsRepositoryInterface;

/**
 * Returns stat cards as an HTMX fragment or JSON.
 */
final class GetDashboardStats
{
    private const string COMPONENT = __DIR__ . '/../Views/Components/stat-cards.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly ?StatsRepositoryInterface $statsRepo = null,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        if ($this->statsRepo === null) {
            throw new RuntimeException('StatsRepository not configured');
        }

        $stats = $this->statsRepo->findAll();

        return $this->responder->respond(
            viewPath: self::COMPONENT,
            layoutPath: null,
            data: ['stats' => $stats],
            json: fn() => array_map(fn(Stat $s) => [
                'key' => $s->key,
                'label' => $s->label,
                'value' => $s->value,
                'icon' => $s->icon,
                'trend' => $s->trend,
                'up' => $s->up,
            ], $stats),
        );
    }

    }
