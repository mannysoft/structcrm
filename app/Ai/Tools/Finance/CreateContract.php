<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\CreateContractAction;
use App\Domains\Finance\DTOs\ContractData;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\Finance\Models\Contract;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateContract implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a contract for a project with its value, status and optional signing date. The contract number is generated automatically.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'signed_at' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Contract::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? ContractStatus::from($validated['status'])
                    : ContractStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported contract status. Use one of: '.implode(', ', array_column(ContractStatus::cases(), 'value')).'.');
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $contract = app(CreateContractAction::class)->execute(
                new ContractData(
                    projectId: $project->id,
                    title: $validated['title'],
                    contractValue: new Money($this->toCents($validated['contract_value']), $this->currency()),
                    status: $status,
                    signedAt: filled($validated['signed_at'] ?? null) ? $this->parseDate($validated['signed_at']) : null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Contract created.',
                'contract' => $this->contractSummary($contract->fresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Project UUID or project number.')->required(),
            'title' => $schema->string()->description('Contract title.')->required(),
            'contract_value' => $schema->number()->description('Contract value in major currency units.')->required(),
            'status' => $schema->string()->enum(array_column(ContractStatus::cases(), 'value'))->description('Defaults to draft.'),
            'signed_at' => $schema->string()->description('Optional signing date (YYYY-MM-DD).'),
        ];
    }
}
