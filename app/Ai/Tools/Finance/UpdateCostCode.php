<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Actions\UpdateCostCodeAction;
use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateCostCode implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a cost code: code, name, type or parent cost code. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'cost_code' => ['required', 'string'],
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'parent' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $costCode = $this->findRecord(CostCode::class, $validated['cost_code'], 'cost code', ['code']);
            $this->authorize('update', $costCode);

            try {
                $type = filled($validated['type'] ?? null)
                    ? CostCodeType::from($validated['type'])
                    : $costCode->type;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported cost code type. Use one of: '.implode(', ', array_column(CostCodeType::cases(), 'value')).'.');
            }

            $parentId = filled($validated['parent'] ?? null)
                ? $this->findRecord(CostCode::class, $validated['parent'], 'cost code', ['code'])->id
                : $costCode->parent_id;

            $updated = app(UpdateCostCodeAction::class)->execute($costCode, new CostCodeData(
                code: (string) ($validated['code'] ?? $costCode->code),
                name: (string) ($validated['name'] ?? $costCode->name),
                type: $type,
                parentId: $parentId,
            ));

            return [
                'message' => 'Cost code updated.',
                'cost_code' => $this->costCodeSummary($updated->fresh('parent')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'cost_code' => $schema->string()->description('Cost code UUID or code.')->required(),
            'code' => $schema->string()->description('New code.'),
            'name' => $schema->string()->description('New name.'),
            'type' => $schema->string()->enum(array_column(CostCodeType::cases(), 'value'))->description('New type.'),
            'parent' => $schema->string()->description('New parent cost code UUID or code.'),
        ];
    }
}
