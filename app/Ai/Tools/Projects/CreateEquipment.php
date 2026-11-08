<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\CreateEquipmentAction;
use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateEquipment implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Add a piece of equipment to the company fleet with its type, status and optional service due date.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'status' => ['nullable', 'string'],
            'service_due_at' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Equipment::class);

            try {
                $type = EquipmentType::from($validated['type']);
                $status = filled($validated['status'] ?? null)
                    ? EquipmentStatus::from($validated['status'])
                    : EquipmentStatus::Available;
            } catch (Throwable) {
                throw new ToolFailure(
                    'Invalid equipment type or status. Types: '.implode(', ', array_column(EquipmentType::cases(), 'value'))
                    .'. Statuses: '.implode(', ', array_column(EquipmentStatus::cases(), 'value')).'.'
                );
            }

            $equipment = app(CreateEquipmentAction::class)->execute(
                new EquipmentData(
                    name: $validated['name'],
                    type: $type,
                    status: $status,
                    serviceDueAt: filled($validated['service_due_at'] ?? null)
                        ? $this->parseDate($validated['service_due_at'])
                        : null,
                    notes: $validated['notes'] ?? null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Equipment added.',
                'equipment' => $this->equipmentSummary($equipment->fresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Equipment name.')->required(),
            'type' => $schema->string()->enum(array_column(EquipmentType::cases(), 'value'))->description('Equipment type.')->required(),
            'status' => $schema->string()->enum(array_column(EquipmentStatus::cases(), 'value'))->description('Defaults to available.'),
            'service_due_at' => $schema->string()->description('Optional next service date (YYYY-MM-DD).'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
