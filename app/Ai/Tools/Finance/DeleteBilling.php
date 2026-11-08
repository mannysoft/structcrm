<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\DeleteBillingAction;
use App\Domains\Finance\Models\Billing;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteBilling implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a progress billing together with its line items and recorded payments. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a billing also deletes its recorded payments.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'billing' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $billing = $this->findRecord(Billing::class, $validated['billing'], 'billing', ['billing_number']);
            $this->authorize('delete', $billing);

            $summary = $this->billingSummary($billing->load(['payments', 'items']));

            app(DeleteBillingAction::class)->execute($billing);

            return [
                'message' => 'Billing deleted.',
                'billing' => $summary,
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
        ];
    }
}
