<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\DeleteContractAction;
use App\Domains\Finance\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteContract implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a contract. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a contract removes the agreed project value from the record.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'contract' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $contract = $this->findRecord(Contract::class, $validated['contract'], 'contract', ['contract_number']);
            $this->authorize('delete', $contract);

            $summary = $this->contractSummary($contract);

            app(DeleteContractAction::class)->execute($contract);

            return [
                'message' => 'Contract deleted.',
                'contract' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'contract' => $schema->string()->description('Contract UUID or contract number.')->required(),
        ];
    }
}
