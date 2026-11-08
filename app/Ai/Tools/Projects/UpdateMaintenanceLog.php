<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Actions\UpdateMaintenanceLogAction;
use App\Domains\ProjectManagement\DTOs\MaintenanceLogData;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateMaintenanceLog implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a maintenance log: equipment, service date, work description or cost. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'log' => ['required', 'string'],
            'equipment' => ['nullable', 'string'],
            'service_date' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $log = $this->findRecord(MaintenanceLog::class, $validated['log'], 'maintenance log');
            $this->authorize('update', $log);

            $equipmentId = filled($validated['equipment'] ?? null)
                ? $this->findRecord(Equipment::class, $validated['equipment'], 'equipment', ['name'])->id
                : $log->equipment_id;

            $updated = app(UpdateMaintenanceLogAction::class)->execute($log, new MaintenanceLogData(
                equipmentId: $equipmentId,
                serviceDate: filled($validated['service_date'] ?? null)
                    ? $this->parseDate($validated['service_date'])
                    : $log->service_date,
                description: (string) ($validated['description'] ?? $log->description),
                cost: isset($validated['cost'])
                    ? new Money($this->toCents($validated['cost']), $this->currency())
                    : $log->cost,
            ));

            return [
                'message' => 'Maintenance log updated.',
                'log' => $this->maintenanceLogSummary($updated->fresh('equipment')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'log' => $schema->string()->description('Maintenance log UUID.')->required(),
            'equipment' => $schema->string()->description('New equipment UUID or exact name.'),
            'service_date' => $schema->string()->description('New service date (YYYY-MM-DD).'),
            'description' => $schema->string()->description('New description of work performed.'),
            'cost' => $schema->number()->description('New maintenance cost in major currency units.'),
        ];
    }
}
