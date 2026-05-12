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
use FrankenForge\Domains\Dashboard\Entities\Invoice;
use FrankenForge\Domains\Dashboard\Entities\InvoiceStatus;

/**
 * Returns recent invoices as an HTMX fragment or JSON.
 */
final class GetRecentActivity
{
    private const string COMPONENT = __DIR__ . '/../Views/Components/invoice-table.html.php';

    public function __construct(
        private readonly Responder $responder,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $invoices = $this->demoInvoices();

        return $this->responder->respond(
            viewPath: self::COMPONENT,
            layoutPath: null,
            data: ['invoices' => $invoices],
            json: fn() => array_map(fn(Invoice $i) => [
                'id' => $i->id,
                'customerName' => $i->customerName,
                'amountCents' => $i->amountCents,
                'currency' => $i->currency,
                'issuedAt' => $i->issuedAt->format('Y-m-d\TH:i:sP'),
                'status' => $i->status->value,
            ], $invoices),
        );
    }

    /**
     * @return list<Invoice>
     */
    private function demoInvoices(): array
    {
        $now = new \DateTimeImmutable();

        return [
            new Invoice('1042', 'Acme Corp',       1250000, 'USD', $now->modify('-2 days'),  InvoiceStatus::Paid),
            new Invoice('1041', 'Globex Inc',       875000, 'USD', $now->modify('-5 days'),  InvoiceStatus::Pending),
            new Invoice('1040', 'Initech',          340000, 'USD', $now->modify('-12 days'), InvoiceStatus::Overdue),
            new Invoice('1039', 'Umbrella Co',     2100000, 'USD', $now->modify('-18 days'), InvoiceStatus::Paid),
            new Invoice('1038', 'Stark Industries', 560000, 'USD', $now->modify('-25 days'), InvoiceStatus::Draft),
        ];
    }
}
