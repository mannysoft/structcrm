<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BillingPayment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListBillingPayments implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List recorded billing payments with their billing number, date, amount, method and reference. Filter by billing, project or a reference/search term.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Billing::class);

            $billingId = filled($request['billing'] ?? null)
                ? $this->findRecord(Billing::class, (string) $request['billing'], 'billing', ['billing_number'])->id
                : null;

            $search = trim((string) ($request['search'] ?? ''));

            $payments = BillingPayment::query()
                ->with('billing')
                ->whereHas('billing', fn ($query) => $query->where('company_id', $this->companyId()))
                ->when($billingId !== null, fn ($query) => $query->where('billing_id', $billingId))
                ->when($search !== '', fn ($query) => $query->where(
                    fn ($inner) => $inner->where('reference', 'like', "%{$search}%")->orWhere('method', 'like', "%{$search}%")
                ))
                ->latest('payment_date')
                ->limit($this->limit($request['limit'] ?? null))
                ->get();

            return [
                'count' => $payments->count(),
                'total' => $this->toDecimal((int) $payments->sum('amount')),
                'payments' => $payments->map(fn (BillingPayment $payment) => [
                    'uuid' => $payment->uuid,
                    'billing_number' => $payment->billing?->billing_number,
                    'billing_uuid' => $payment->billing?->uuid,
                    'date' => $payment->payment_date?->toDateString(),
                    'amount' => $this->toDecimal($payment->amount),
                    'currency' => $payment->billing?->currency,
                    'method' => $payment->method,
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                ])->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'billing' => $schema->string()->description('Optional billing UUID or billing number.'),
            'search' => $schema->string()->description('Optional payment reference or method fragment.'),
            'limit' => $schema->integer()->description('Maximum payments to return (default 25, max 100).'),
        ];
    }
}
