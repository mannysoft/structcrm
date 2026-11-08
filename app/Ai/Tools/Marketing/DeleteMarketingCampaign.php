<?php

declare(strict_types=1);

namespace App\Ai\Tools\Marketing;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Marketing\Actions\DeleteMarketingCampaignAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteMarketingCampaign implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a marketing campaign and its steps. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a campaign stops its drip sequence and removes its email steps.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'campaign' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $campaign = $this->findMarketingCampaign($validated['campaign']);
            $this->authorize('delete', $campaign);

            $summary = $this->campaignSummary($campaign->load('steps'));

            app(DeleteMarketingCampaignAction::class)->execute($campaign);

            return [
                'message' => 'Marketing campaign deleted.',
                'campaign' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'campaign' => $schema->string()->description('Campaign UUID or exact name.')->required(),
        ];
    }
}
