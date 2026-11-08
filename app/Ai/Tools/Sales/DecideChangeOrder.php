<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Sales\Actions\DecideChangeOrderAction;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class DecideChangeOrder implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Approve or reject a pending change order. Approving revises the project budget. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Approving a change order revises the project budget.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'change_order' => ['required', 'string'],
            'decision' => ['required', 'string', 'in:approved,rejected'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $changeOrder = $this->findRecord(ChangeOrder::class, $validated['change_order'], 'change order', ['co_number']);
            $this->authorize('update', $changeOrder);

            try {
                $status = ChangeOrderStatus::from($validated['decision']);
            } catch (Throwable) {
                throw new ToolFailure('Decision must be either approved or rejected.');
            }

            $updated = app(DecideChangeOrderAction::class)->execute($changeOrder, $status);

            return [
                'message' => "Change order {$updated->co_number} {$status->value}.",
                'change_order' => $this->changeOrderSummary($updated->fresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'change_order' => $schema->string()->description('Change order UUID or CO number.')->required(),
            'decision' => $schema->string()->enum(['approved', 'rejected'])->description('Approval decision.')->required(),
        ];
    }
}
