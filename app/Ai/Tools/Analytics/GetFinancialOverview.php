<?php

declare(strict_types=1);

namespace App\Ai\Tools\Analytics;

use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Billing;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Expense;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetFinancialOverview implements Tool
{
    use InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Summarise finances for an optional date range: progress billings (gross, retention, net, collected, outstanding), invoices by status, expenses, and approved change-order value. Use for revenue, cash collection and cost questions.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $companyId = $this->companyId();
            $from = $validated['date_from'] ?? null;
            $to = $validated['date_to'] ?? null;

            $billings = Billing::query()
                ->where('company_id', $companyId)
                ->when($from, fn ($query) => $query->whereDate('period_start', '>=', $from))
                ->when($to, fn ($query) => $query->whereDate('period_end', '<=', $to))
                ->with('payments')
                ->get();

            $paid = $billings->sum(fn (Billing $billing) => $billing->payments->sum('amount'));
            $net = (int) $billings->sum('net_amount');

            $invoices = Invoice::query()
                ->where('company_id', $companyId)
                ->when($from, fn ($query) => $query->whereDate('issue_date', '>=', $from))
                ->when($to, fn ($query) => $query->whereDate('issue_date', '<=', $to))
                ->get();

            $expenses = Expense::query()
                ->where('company_id', $companyId)
                ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
                ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
                ->get();

            $approvedChangeOrders = (int) ChangeOrder::query()
                ->where('company_id', $companyId)
                ->where('status', ChangeOrderStatus::Approved->value)
                ->sum('amount');

            return [
                'currency' => $this->currency(),
                'date_from' => $from,
                'date_to' => $to,
                'billings' => [
                    'count' => $billings->count(),
                    'gross' => $this->toDecimal((int) $billings->sum('gross_amount')),
                    'retention' => $this->toDecimal((int) $billings->sum('retention_amount')),
                    'deductions' => $this->toDecimal((int) $billings->sum('other_deductions_amount')),
                    'net' => $this->toDecimal($net),
                    'collected' => $this->toDecimal((int) $paid),
                    'outstanding' => $this->toDecimal($net - (int) $paid),
                ],
                'invoices' => [
                    'count' => $invoices->count(),
                    'total' => $this->toDecimal((int) $invoices->sum('total')),
                    'by_status' => $invoices
                        ->groupBy(fn (Invoice $invoice) => $this->enumValue($invoice->status))
                        ->map(fn ($group, $status) => [
                            'status' => $status,
                            'count' => $group->count(),
                            'total' => $this->toDecimal((int) $group->sum('total')),
                        ])
                        ->values()
                        ->all(),
                ],
                'expenses' => [
                    'count' => $expenses->count(),
                    'total' => $this->toDecimal((int) $expenses->sum('amount')),
                ],
                'approved_change_orders_total' => $this->toDecimal($approvedChangeOrders),
                'net_after_costs' => $this->toDecimal($net - (int) $expenses->sum('amount')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date_from' => $schema->string()->description('Optional start date (YYYY-MM-DD).'),
            'date_to' => $schema->string()->description('Optional end date (YYYY-MM-DD).'),
        ];
    }
}
