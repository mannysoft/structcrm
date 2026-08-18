<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\InvoiceData;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class UpdateInvoiceAction
{
    public function execute(Invoice $invoice, InvoiceData $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $total = collect($data->items)->sum(
                fn (array $item) => $item['quantity'] * $item['unit_price'],
            );

            $invoice->update([
                'project_id' => $data->projectId,
                'status' => $data->status,
                'total' => $total,
                'issue_date' => $data->issueDate,
                'due_date' => $data->dueDate,
            ]);

            $invoice->items()->delete();

            foreach ($data->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $invoice->load('items');
        });
    }
}
