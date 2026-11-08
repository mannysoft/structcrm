<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListMaintenanceLogs implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List equipment maintenance logs with service dates, descriptions and costs, optionally filtered by equipment.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', MaintenanceLog::class);

            $query = MaintenanceLog::query()
                ->with('equipment')
                ->where('company_id', $this->companyId());

            if (filled($request['equipment'] ?? null)) {
                $equipment = $this->findRecord(Equipment::class, (string) $request['equipment'], 'equipment', ['name']);
                $query->where('equipment_id', $equipment->id);
            }

            $logs = $query->orderByDesc('service_date')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $logs->count(),
                'total_cost' => $this->toDecimal((int) $logs->sum('cost_amount')),
                'logs' => $logs->map(fn (MaintenanceLog $log) => $this->maintenanceLogSummary($log))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'equipment' => $schema->string()->description('Optional equipment UUID or exact name.'),
            'limit' => $schema->integer()->description('Maximum logs to return (default 25, max 100).'),
        ];
    }
}
