<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class DeleteInvoiceAction
{
    public function execute(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->delete();
        });
    }
}
