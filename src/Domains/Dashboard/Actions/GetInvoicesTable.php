<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Actions;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;
use FrankenForge\Core\View\Responder;
use FrankenForge\Domains\Dashboard\Repositories\InvoiceRepositoryInterface;

/**
 * Returns invoices table as an HTMX fragment.
 */
final class GetInvoicesTable
{
    private const string VIEW = __DIR__ . '/../Views/Components/invoices-table.html.php';

    public function __construct(
        private readonly Responder $responder,
        private readonly InvoiceRepositoryInterface $invoicesRepo,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $invoices = $this->invoicesRepo->findAll();

        return $this->responder->respond(
            viewPath: self::VIEW,
            layoutPath: null,
            data: ['invoices' => $invoices],
        );
    }
}