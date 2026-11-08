<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListEquipment implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List company equipment with type, availability status and next service date. Filter by status, type or name.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Equipment::class);

            $search = trim((string) ($request['search'] ?? ''));

            $equipment = Equipment::query()
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where('name', 'like', "%{$search}%"))
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']))
                ->when(filled($request['type'] ?? null), fn ($builder) => $builder->where('type', $request['type']))
                ->orderBy('name')
                ->limit($this->limit($request['limit'] ?? null))
                ->get();

            return [
                'count' => $equipment->count(),
                'equipment' => $equipment->map(fn (Equipment $item) => $this->equipmentSummary($item))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional equipment name fragment.'),
            'status' => $schema->string()->enum(array_column(EquipmentStatus::cases(), 'value'))->description('Optional status filter.'),
            'type' => $schema->string()->enum(array_column(EquipmentType::cases(), 'value'))->description('Optional type filter.'),
            'limit' => $schema->integer()->description('Maximum items to return (default 25, max 100).'),
        ];
    }
}
