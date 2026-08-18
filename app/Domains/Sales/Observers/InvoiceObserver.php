<?php

declare(strict_types=1);

namespace App\Domains\Sales\Observers;

use App\Domains\Sales\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->invoice_number)) {
            $invoice->invoice_number = $this->nextNumber($invoice->company_id);
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = Invoice::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('INV-%d', $count + 1);
    }
}
