<?php
/**
 * FrankenForge — FrankenForge\Shared\Infrastructure\Database
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Shared\Infrastructure\Database;

use FrankenForge\Domains\Dashboard\Entities\Invoice;
use FrankenForge\Domains\Dashboard\Entities\InvoiceStatus;
use FrankenForge\Domains\Dashboard\Repositories\InvoiceRepositoryInterface;

/**
 * SQLite implementation of InvoiceRepositoryInterface.
 *
 * Uses a simple 'invoices' table with columns:
 *   - id (string, primary key)
 *   - customer_name (string)
 *   - amount_cents (integer)
 *   - currency (string)
 *   - issued_at (datetime)
 *   - status (string)
 */
final class SqliteInvoiceRepository implements InvoiceRepositoryInterface
{
    private const string TABLE = 'invoices';

    public function __construct(
        private readonly Connection $db,
    ) {}

    public function findAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY issued_at DESC");

        return array_map(fn(array $row) => $this->toEntity($row), $rows);
    }

    public function findById(string $id): ?Invoice
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE id = :id",
            ['id' => $id],
        );

        if ($row === null) {
            return null;
        }

        return $this->toEntity($row);
    }

    public function save(Invoice $invoice): void
    {
        $existing = $this->findById($invoice->id);

        if ($existing === null) {
            $this->db->insert(self::TABLE, $this->toRow($invoice));
        } else {
            $this->db->update(
                self::TABLE,
                $this->toRow($invoice),
                'id = :id',
                ['id' => $invoice->id],
            );
        }
    }

    public function delete(string $id): void
    {
        $this->db->delete(self::TABLE, 'id = :id', ['id' => $id]);
    }

    /**
     * @param array $row
     * @return Invoice
     * @throws \DateMalformedStringException
     */
    private function toEntity(array $row): Invoice
    {
        return new Invoice(
            id: $row['id'],
            customerName: $row['customer_name'],
            amountCents: (int) $row['amount_cents'],
            currency: $row['currency'],
            issuedAt: new \DateTimeImmutable($row['issued_at']),
            status: InvoiceStatus::tryFrom($row['status']) ?? InvoiceStatus::Draft,
        );
    }

    /**
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    private function toRow(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'customer_name' => $invoice->customerName,
            'amount_cents' => $invoice->amountCents,
            'currency' => $invoice->currency,
            'issued_at' => $invoice->issuedAt->format('Y-m-d'),
            'status' => $invoice->status->value,
        ];
    }
}
