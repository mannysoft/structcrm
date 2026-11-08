<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\DeleteLeadAction;
use App\Domains\Sales\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteLead implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a sales lead. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a lead removes it from the sales pipeline.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'lead' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $lead = $this->findRecord(Lead::class, $validated['lead'], 'lead', ['email']);
            $this->authorize('delete', $lead);

            $summary = $this->leadSummary($lead);

            app(DeleteLeadAction::class)->execute($lead);

            return [
                'message' => 'Lead deleted.',
                'lead' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'lead' => $schema->string()->description('Lead UUID or email.')->required(),
        ];
    }
}
