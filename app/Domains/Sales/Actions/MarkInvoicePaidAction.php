<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;

final class MarkInvoicePaidAction
{
    /**
     * Mark an invoice as fully paid.
     */
    public function execute(Invoice $invoice): Invoice
    {
        $invoice->forceFill([
            'status' => InvoiceStatus::Paid,
        ])->save();

        return $invoice;
    }
}
