<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Dashboard\Repositories\UserRepositoryInterface;

/**
 * Returns user table as an HTMX fragment.
 */
final class GetUsersTable
{
    private const string VIEW = __DIR__ . '/../Views/Components/users-table.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly UserRepositoryInterface $usersRepo,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $users = $this->usersRepo->findAll();

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: null,
            data: ['users' => $users],
        );
    }
}