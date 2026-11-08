<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\RecordBillingPaymentAction;
use App\Domains\Finance\Models\Billing;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class RecordBillingPayment implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Record a payment received against a billing and update its status to partially paid or paid. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Recording a payment changes cash collection records.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'billing' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['nullable', 'string'],
            'method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorizePermission('manage_payments');

            $billing = $this->findRecord(Billing::class, $validated['billing'], 'billing', ['billing_number']);

            $updated = app(RecordBillingPaymentAction::class)->execute(
                $billing,
                filled($validated['payment_date'] ?? null)
                    ? $this->parseDate($validated['payment_date'])
                    : $this->parseDate(today()->toDateString()),
                $this->toCents($validated['amount']),
                $validated['method'] ?? null,
                $validated['reference'] ?? null,
                $validated['notes'] ?? null,
            );

            return [
                'message' => "Payment recorded against billing {$updated->billing_number}.",
                'billing' => $this->billingSummary($updated->fresh(['payments'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'billing' => $schema->string()->description('Billing UUID or billing number.')->required(),
            'amount' => $schema->number()->description('Payment amount in major currency units.')->required(),
            'payment_date' => $schema->string()->description('Payment date (YYYY-MM-DD). Defaults to today.'),
            'method' => $schema->string()->description('Payment method, e.g. bank transfer, cheque, cash.'),
            'reference' => $schema->string()->description('Payment reference or cheque number.'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
