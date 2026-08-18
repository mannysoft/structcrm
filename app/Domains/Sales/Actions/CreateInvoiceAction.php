<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\InvoiceData;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class CreateInvoiceAction
{
    public function execute(InvoiceData $data, int $companyId): Invoice
    {
        return DB::transaction(function () use ($data, $companyId) {
            $total = collect($data->items)->sum(
                fn (array $item) => $item['quantity'] * $item['unit_price'],
            );

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'status' => $data->status,
                'total' => $total,
                'issue_date' => $data->issueDate,
                'due_date' => $data->dueDate,
            ]);

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
