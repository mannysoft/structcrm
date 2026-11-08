<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\UpdateContractAction;
use App\Domains\Finance\DTOs\ContractData;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\Finance\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateContract implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a contract: title, value, status or signing date. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'contract' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'signed_at' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $contract = $this->findRecord(Contract::class, $validated['contract'], 'contract', ['contract_number']);
            $this->authorize('update', $contract);

            try {
                $status = filled($validated['status'] ?? null)
                    ? ContractStatus::from($validated['status'])
                    : $contract->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported contract status. Use one of: '.implode(', ', array_column(ContractStatus::cases(), 'value')).'.');
            }

            $updated = app(UpdateContractAction::class)->execute($contract, new ContractData(
                projectId: $contract->project_id,
                title: (string) ($validated['title'] ?? $contract->title),
                contractValue: isset($validated['contract_value'])
                    ? new Money($this->toCents($validated['contract_value']), $this->currency())
                    : $contract->contractValue,
                status: $status,
                signedAt: filled($validated['signed_at'] ?? null)
                    ? $this->parseDate($validated['signed_at'])
                    : $contract->signed_at,
            ));

            return [
                'message' => 'Contract updated.',
                'contract' => $this->contractSummary($updated->fresh()),
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
            'title' => $schema->string()->description('New title.'),
            'contract_value' => $schema->number()->description('New contract value in major currency units.')->min(0),
            'status' => $schema->string()->enum(array_column(ContractStatus::cases(), 'value'))->description('New status.'),
            'signed_at' => $schema->string()->description('New signing date (YYYY-MM-DD).'),
        ];
    }
}
