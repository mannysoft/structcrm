<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Actions\CreateCostCodeAction;
use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateCostCode implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a cost code (material, labour, equipment, subcontract, overhead or other), optionally under a parent cost code.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'parent' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', CostCode::class);

            try {
                $type = filled($validated['type'] ?? null)
                    ? CostCodeType::from($validated['type'])
                    : CostCodeType::Other;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported cost code type. Use one of: '.implode(', ', array_column(CostCodeType::cases(), 'value')).'.');
            }

            $parentId = filled($validated['parent'] ?? null)
                ? $this->findRecord(CostCode::class, $validated['parent'], 'cost code', ['code'])->id
                : null;

            $costCode = app(CreateCostCodeAction::class)->execute(
                new CostCodeData(
                    code: $validated['code'],
                    name: $validated['name'],
                    type: $type,
                    parentId: $parentId,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Cost code created.',
                'cost_code' => $this->costCodeSummary($costCode->load('parent')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'code' => $schema->string()->description('Unique cost code, e.g. 03-100.')->required(),
            'name' => $schema->string()->description('Cost code name.')->required(),
            'type' => $schema->string()->enum(array_column(CostCodeType::cases(), 'value'))->description('Defaults to other.'),
            'parent' => $schema->string()->description('Optional parent cost code UUID or code.'),
        ];
    }
}
