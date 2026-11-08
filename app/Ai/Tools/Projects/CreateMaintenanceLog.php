<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Actions\CreateMaintenanceLogAction;
use App\Domains\ProjectManagement\DTOs\MaintenanceLogData;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateMaintenanceLog implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Record maintenance performed on a piece of equipment, including the service date, description and cost.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'equipment' => ['required', 'string'],
            'service_date' => ['required', 'string'],
            'description' => ['required', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', MaintenanceLog::class);

            $equipment = $this->findRecord(Equipment::class, $validated['equipment'], 'equipment', ['name']);

            $log = app(CreateMaintenanceLogAction::class)->execute(
                new MaintenanceLogData(
                    equipmentId: $equipment->id,
                    serviceDate: $this->parseDate($validated['service_date']),
                    description: $validated['description'],
                    cost: new Money($this->toCents($validated['cost'] ?? 0), $this->currency()),
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Maintenance log recorded.',
                'log' => $this->maintenanceLogSummary($log->fresh('equipment')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'equipment' => $schema->string()->description('Equipment UUID or exact name.')->required(),
            'service_date' => $schema->string()->description('Service date (YYYY-MM-DD).')->required(),
            'description' => $schema->string()->description('Work performed.')->required(),
            'cost' => $schema->number()->description('Maintenance cost in major currency units.'),
        ];
    }
}
