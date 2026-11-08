<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\DeleteSupplierAction;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteSupplier implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a supplier. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a supplier removes it from purchasing.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'supplier' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $supplier = $this->findRecord(Supplier::class, $validated['supplier'], 'supplier', ['name']);
            $this->authorize('delete', $supplier);

            $summary = $this->supplierSummary($supplier);

            app(DeleteSupplierAction::class)->execute($supplier);

            return [
                'message' => 'Supplier deleted.',
                'supplier' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'supplier' => $schema->string()->description('Supplier UUID or name.')->required(),
        ];
    }
}
