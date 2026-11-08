<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListCostCodes implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List cost codes with their type and parent code. Filter by type or search by code or name.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', CostCode::class);

            $search = trim((string) ($request['search'] ?? ''));

            $costCodes = CostCode::query()
                ->with('parent')
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where(
                    fn ($inner) => $inner->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")
                ))
                ->when(filled($request['type'] ?? null), fn ($builder) => $builder->where('type', $request['type']))
                ->orderBy('code')
                ->limit($this->limit($request['limit'] ?? null, 50, 200))
                ->get();

            return [
                'count' => $costCodes->count(),
                'cost_codes' => $costCodes->map(fn (CostCode $costCode) => $this->costCodeSummary($costCode))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional code or name fragment.'),
            'type' => $schema->string()->enum(array_column(CostCodeType::cases(), 'value'))->description('Optional type filter.'),
            'limit' => $schema->integer()->description('Maximum cost codes to return (default 50, max 200).'),
        ];
    }
}
