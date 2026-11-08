<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\UpdateSupplierAction;
use App\Domains\Sales\DTOs\SupplierData;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateSupplier implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a supplier record. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'supplier' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $supplier = $this->findRecord(Supplier::class, $validated['supplier'], 'supplier', ['name']);
            $this->authorize('update', $supplier);

            $updated = app(UpdateSupplierAction::class)->execute($supplier, new SupplierData(
                name: (string) ($validated['name'] ?? $supplier->name),
                email: $validated['email'] ?? $supplier->email,
                phone: $validated['phone'] ?? $supplier->phone,
                address: $validated['address'] ?? $supplier->address,
                notes: $validated['notes'] ?? $supplier->notes,
            ));

            return [
                'message' => 'Supplier updated.',
                'supplier' => $this->supplierSummary($updated->fresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'supplier' => $schema->string()->description('Supplier UUID or name.')->required(),
            'name' => $schema->string()->description('New name.'),
            'email' => $schema->string()->description('New email.'),
            'phone' => $schema->string()->description('New phone.'),
            'address' => $schema->string()->description('New address.'),
            'notes' => $schema->string()->description('New notes.'),
        ];
    }
}
