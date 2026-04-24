<?php

declare(strict_types=1);

namespace FrankenForge\Domains\Dashboard\Repositories;

use FrankenForge\Domains\Dashboard\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    public function findAll(): array;

    public function findById(string $id): ?Invoice;

    public function save(Invoice $invoice): void;

    public function delete(string $id): void;
}