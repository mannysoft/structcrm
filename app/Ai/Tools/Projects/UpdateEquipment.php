<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\UpdateEquipmentAction;
use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateEquipment implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update equipment details: name, type, status (available, in_use, maintenance, retired), service due date or notes.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'equipment' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'service_due_at' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $equipment = $this->findRecord(Equipment::class, $validated['equipment'], 'equipment', ['name']);
            $this->authorize('update', $equipment);

            try {
                $type = filled($validated['type'] ?? null) ? EquipmentType::from($validated['type']) : $equipment->type;
                $status = filled($validated['status'] ?? null) ? EquipmentStatus::from($validated['status']) : $equipment->status;
            } catch (Throwable) {
                throw new ToolFailure(
                    'Invalid equipment type or status. Types: '.implode(', ', array_column(EquipmentType::cases(), 'value'))
                    .'. Statuses: '.implode(', ', array_column(EquipmentStatus::cases(), 'value')).'.'
                );
            }

            $updated = app(UpdateEquipmentAction::class)->execute($equipment, new EquipmentData(
                name: (string) ($validated['name'] ?? $equipment->name),
                type: $type,
                status: $status,
                serviceDueAt: filled($validated['service_due_at'] ?? null)
                    ? $this->parseDate($validated['service_due_at'])
                    : $equipment->service_due_at,
                notes: $validated['notes'] ?? $equipment->notes,
            ));

            return [
                'message' => 'Equipment updated.',
                'equipment' => $this->equipmentSummary($updated->fresh()),
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
            'name' => $schema->string()->description('New name.'),
            'type' => $schema->string()->enum(array_column(EquipmentType::cases(), 'value'))->description('New type.'),
            'status' => $schema->string()->enum(array_column(EquipmentStatus::cases(), 'value'))->description('New status.'),
            'service_due_at' => $schema->string()->description('New service due date (YYYY-MM-DD).'),
            'notes' => $schema->string()->description('New notes.'),
        ];
    }
}
